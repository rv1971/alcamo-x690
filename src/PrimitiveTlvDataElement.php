<?php

namespace alcamo\x690;

use alcamo\data_element\{DataElementInterface, SerializerInterface};
use alcamo\exception\DataValidationFailed;

/**
 * @brief Primitive TLV data element
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-05-14
 */
class PrimitiveTlvDataElement extends AbstractTlvDataElement
{
    private $serializer_; ///< SerializerInterface
    private $format_;     ///< string

    public function __construct(
        Tag $tag,
        DataElementInterface $dataElement,
        SerializerInterface $serializer,
        ?string $formatWithoutLength = null
    ) {
        parent::__construct($tag, $dataElement);

        if ($this->tag_->isConstructed()) {
            /** @throw alcamo::exception::DataValidationFailed if tag in
             *  $tlvDataElement is not constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "tag '{$this->tag_}' is constructed" ]
            );
        }

        if (
            !$this->dataElement_->getDatatype()->isEqualToOrDerivedFrom(
                $serializer->getDatatype()->getXName()
            )
        ) {
            /** @throw alcamo::exception::DataValidationFailed if the
             *  data element datatype it not equal to or derived from the
             *  serializer datatype. */
            throw (new DataValidationFailed())->setMessageContext(
                [
                    'extraMessage' => 'the data element datatype '
                        . $this->dataElement_->getDatatype()->getXName()
                        . ' is not equal to or derived from '
                        . 'the serializer datatype '
                        . $serializer->getDatatype()->getXName()
                ]
            );
        }

        $this->serializer_ = $serializer;

        if (isset($formatWithoutLength)) {
            $this->format_ = $formatWithoutLength;
        } else {
            switch ($serializer->getEncodingParams()->getEncoding()) {
                case 'BCD':
                    $this->format_ = 'n';
                    break;

                case 'COMPRESSED-BCD':
                    $this->format_ = 'cn';
                    break;

                case 'ASCII':
                case 'EBCDIC':
                case 'UTF-8':
                    $this->format_ = 'ans';
                    break;

                default:
                    $this->format_ = 'b';
            }
        }

        if ($serializer->getLengthRange()) {
            $this->format_ .= ' ' . $serializer->getLengthRange();
        }
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer_;
    }

    public function getFormat(): string
    {
        return $this->format_;
    }
}
