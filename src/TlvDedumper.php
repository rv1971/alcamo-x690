<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\exception\{ExceptionInterface, SyntaxError};
use alcamo\input_stream\StringInputStream;

/**
 * @brief Dedumper for TLV data element instances
 *
 * @date Last reviewed 2026-06-08
 */
class TlvDedumper
{
    private $tlvDeDict_;     ///< TlvDeDictInterface
    private $tlvSerializer_; ///< TlvSerializer

    public function __construct(?TlvDeDictInterface $tlvDeDict = null)
    {
        $this->tlvDeDict_ =
            $tlvDeDict ?? new UniversalClassTlvDataElementDict();

        $this->tlvSerializer_ = new TlvSerializer();
    }

    public function getTlvDeDict(): TlvDeDictInterface
    {
        return $this->tlvDeDict_;
    }

    public function dedumpTlvDeInstance(
        StringInputStream $istream,
        ?string $parent = null
    ): ?AbstractTlvDeInstance {
        $istream->extractWsAndComments();

        if (!$istream->isGood()) {
            return null;
        }

        /* This also extracts any whitespace after the tag. */
        $tag = Tag::newFromHex($istream->extractHexDigits());

        try {
            $istream->extractWs();

            $tlvDataElement = $this->tlvDeDict_->lookup($tag, $parent);

            /* Extract optional length */
            if ($istream->peek() == '[') {
                $lengthSpec = $istream->extractUntil(']', null, true);

                if (sscanf($lengthSpec, '[%X]', $length) != 1) {
                    /** @throw alcamo::exception::SyntaxError if a length
                     *  specification could not be read. */
                    throw (new SyntaxError())->setMessageContext(
                        [
                            'inData' => $lengthSpec,
                            'extraMessage' =>
                                'not a hexadecimal number in brackets'
                        ]
                    );
                }

                $istream->extractWs();
            } else {
                $length = null;
            }

            $istream->extractFixedString('<', true);

            $istream->extractWs();

            if ($tag->isConstructed()) {
                $rawContent = '';

                $children =
                    $this->dedumpTlvDeInstances($istream, $tag, $rawContent);

                if (isset($length)) {
                    $rawContent = str_pad($rawContent, $length, "\x00");
                }

                $result = new ConstructedTlvDeInstance(
                    $tlvDataElement,
                    new ImmutableBinaryString($rawContent),
                    $children
                );
            } else {
                if ($istream->peek() == "'") {
                    $istream->extract();

                    $rawContent = ImmutableBinaryString::newFromHex(
                        $istream->extractHexDigits() ?? ''
                    );

                    $result = PrimitiveTlvDeInstance::newFromRawContent(
                        $tlvDataElement,
                        $rawContent
                    );

                    $istream->extractFixedString("'");

                    $istream->extractWs();

                    if (isset($length) && count($rawContent) < $length) {
                        $result = PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDataElement,
                            $result->getLiteral(),
                            $length
                        );
                    }
                } else {
                    $result = PrimitiveTlvDeInstance::newFromLiteral(
                        $tlvDataElement,
                        $tlvDataElement->getSerializer()
                            ->dedumpFromStream($istream),
                        $length
                    );
                }
            }

            $istream->extractFixedString('>', true);

            $istream->extractWs();

            return $result;
        } catch (ExceptionInterface $e) {
            if (!isset($e->getMessageContext()['inTag'])) {
                $e->addMessageContext(
                    [ 'inTag' => $tag ]
                );
            }

            throw $e;
        }
    }

    public function dedumpTlvDeInstances(
        StringInputStream $istream,
        ?string $parent = null,
        ?string &$rawContent = null
    ): array {
        $result = [];

        for ( ; ; ) {
            $istream->extractWsAndComments();

            if (!$istream->isGood() || $istream->peek() == '>') {
                break;
            }

            $deInstance = $this->dedumpTlvDeInstance($istream, $parent);

            $result[] = $deInstance;

            if (isset($rawContent)) {
                $rawContent .=
                    $this->tlvSerializer_->serializeTlvDeInstance($deInstance);
            }
        }

        return $result;
    }
}
