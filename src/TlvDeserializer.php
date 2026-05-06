<?php

namespace alcamo\x690;

use alcamo\binary_data\BinaryString;
use alcamo\exception\Eof;
use alcamo\input_stream\InputStreamInterface;

class TlvDeserializer
{
    private $tlvDataElementDict_; ///< TlvDataElementDictInterface

    public function __construct(TlvDataElementDictInterface $tlvDataElementDict)
    {
        $this->tlvDataElementDict_ = $tlvDataElementDict;
    }

    public function deserializeTag(InputStreamInterface $istream): Tag
    {
        $tag = $istream->extract();

        if (!isset($tag)) {
            /** @throw alcamo::exception::Eof if no data left to read. */
            throw (new Eof())->setMessageContext(
                [
                    'object' => new BinaryString($istream),
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
                            'object' => new BinaryString($istream),
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
                    'object' => new BinaryString($istream),
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
                    'object' => new BinaryString($istream),
                    'atOffset' => $istream->getOffset()
                ]
            );
        }

        if (!isset($lengthData)) {
            /** @throw alcamo::exception::Eof if length cannot be completely
             *  read. */
            throw (new Eof())->setMessageContext(
                [
                    'object' => new BinaryString($istream),
                    'atOffset' => $istream->getOffset(),
                    'requestedUnits' => $lengthByteCount,
                    'availableUnits' => 0
                ]
            );
        }

        /** Lengths up to 4GiB - 1 are supported. */

        /** @todo simplify using always 'N' and padding data? See BinaryString
         *  implementation. */
        switch ($lengthByteCount) {
            case '1':
                return ord($lengthData);

            case '2':
                return unpack('n', $lengthData)[1];

            case '3':
                return unpack('N', "\x00$lengthData")[1];

            case '4':
                return unpack('N', $lengthData)[1];
        }
    }

    public function deserializeDataValue(
        InputStreamInterface $istream,
        ?string $parent = null
    ): DataValue {
        $tag = $this->deserializeTag($istream);

        /** @todo handle end-of-content tag */

        $length = $this->deserializeLength($istream);

        /** @todo handle length -1 */

        $rawContent = $istream->extract($length);

        $tlvDataElement = $this->tlvDataElementDict_->lookup($tag, $parent);

        if ($tag->isConstructed()) {
            $content = [];

            $istream2 = new InputStream($rawContent);

            while ($istream2->isGood()) {
                $content[] = $this->deserializeDataValue($istream2, $tag);
            }

            return new ConstructedDataValue(
                $tlvDataElement,
                $rawContent,
                $content
            );
        } else {
            return PrimitiveDataValue::newFromRawContent(
                $tlvDataElement,
                new BinaryString($rawContent)
            );
        }
    }
}
