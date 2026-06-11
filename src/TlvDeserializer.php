<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\exception\{Eof, OutOfRange};
use alcamo\input_stream\{InputStreamInterface, StringInputStream};

/**
 * @brief Deserializer for TLV data element instances
 *
 * @date Last reviewed 2026-05-06
 */
class TlvDeserializer
{
    private $tlvDeDict_; ///< TlvDeDictInterface

    public function __construct(?TlvDeDictInterface $tlvDeDict = null)
    {
        $this->tlvDeDict_ = $tlvDeDict ?? new UniversalClassTlvDeDict();
    }

    public function getTlvDeDict(): TlvDeDictInterface
    {
        return $this->tlvDeDict_;
    }

    public function deserializeTag(InputStreamInterface $istream): Tag
    {
        $tag = $istream->extract();

        if (!isset($tag)) {
            /** @throw alcamo::exception::Eof if no data left to read. */
            throw (new Eof())->setMessageContext(
                [
                    'object' => new ImmutableBinaryString($istream),
                    'atOffset' => $istream->getOffset(),
                    'requestedUnits' => 1,
                    'availableUnits' => 0
                ]
            );
        }

        if ((ord($tag) & 0x1F) == 0x1F) {
            do {
                $ch = $istream->extract();

                if (!isset($ch)) {
                    /** @throw alcamo::exception::Eof if tag cannot be
                     *  completely read. */
                    throw (new Eof())->setMessageContext(
                        [
                            'object' => new ImmutableBinaryString($istream),
                            'atOffset' => $istream->getOffset(),
                            'requestedUnits' => 1,
                            'availableUnits' => 0
                        ]
                    );
                }

                $tag .= $ch;
            } while (ord($ch) & 0x80);
        }

        return new Tag($tag);
    }

    public function deserializeLength(InputStreamInterface $istream): int
    {
        $ch = $istream->extract();

        if (!isset($ch)) {
            /** @throw alcamo::exception::Eof if no data left to read. */
            throw (new Eof())->setMessageContext(
                [
                    'object' => new ImmutableBinaryString($istream),
                    'atOffset' => $istream->getOffset(),
                    'requestedUnits' => 1,
                    'availableUnits' => 0
                ]
            );
        }

        $length = ord($ch);

        /* When encountering a value less or equal to 0x7F, the short form
         * was used. */
        if ($length <= 0x7F) {
            return $length;
        }

        /** Return -1 for indefinite length. */
        if ($length == 0x80) {
            return -1;
        }

        /* Else parse the long form. */
        $lengthByteCount = $length - 0x80;

        try {
            $lengthData = $istream->extract($lengthByteCount);
        } catch (Eof $e) {
            throw $e->addMessageContext(
                [
                    'object' => new ImmutableBinaryString($istream),
                    'atOffset' => $istream->getOffset()
                ]
            );
        }

        if (!isset($lengthData)) {
            /** @throw alcamo::exception::Eof if length cannot be completely
             *  read. */
            throw (new Eof())->setMessageContext(
                [
                    'object' => new ImmutableBinaryString($istream),
                    'atOffset' => $istream->getOffset(),
                    'requestedUnits' => $lengthByteCount,
                    'availableUnits' => 0
                ]
            );
        }

        /** Lengths up to 4GiB - 1 are supported. */
        return unpack('N', str_pad($lengthData, 4, "\x00", STR_PAD_LEFT))[1];
    }

    public function deserializeTlvDeInstance(
        InputStreamInterface $istream,
        ?string $parent = null
    ): AbstractTlvDeInstance {
        $tag = $this->deserializeTag($istream);

        $length = $this->deserializeLength($istream);

        if ($tag->isEoc()) {
            return $this->tlvDeDict_->getEocDeInstance();
        }

        $tlvDataElement = $this->tlvDeDict_->lookup($tag, $parent);

        if ($tag->isConstructed()) {
            if ($length >= 0) {
                $rawContent = $istream->extract($length);

                $content = $this->deserializeTlvDeInstances(
                    new StringInputStream($rawContent),
                    $tag
                );
            } else {
                $rawContent = '';

                $content = $this
                    ->deserializeDeInstances($istream, $tag, true, $rawContent);
            }

            return new ConstructedTlvDeInstance(
                $tlvDataElement,
                new ImmutableBinaryString($rawContent),
                $content
            );
        } else {
            OutOfRange::throwIfNegative($length);

            $rawContent = $istream->extract($length);

            return PrimitiveTlvDeInstance::newFromRawContent(
                $tlvDataElement,
                new ImmutableBinaryString($rawContent)
            );
        }
    }

    public function deserializeTlvDeInstances(
        InputStreamInterface $istream,
        ?string $parent = null,
        ?bool $stopAtEoc = null,
        ?string &$rawContent = null
    ): array {
        $result = [];

        if ($stopAtEoc) {
            $eocDeInstance = $this->tlvDeDict_->getEocDeInstance();
        }

        while ($istream->isGood()) {
            $deInstance = $this->deserializeTlvDeInstance($istream, $parent);

            if ($stopAtEoc && $deInstance === $eocDeInstance) {
                break;
            }

            $result[] = $deInstance;

            if (isset($rawContent)) {
                $rawContent .= $deInstance->getRawContent();
            }
        }

        return $result;
    }
}
