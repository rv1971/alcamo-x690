<?php

namespace alcamo\x690;

use alcamo\data_element\DataElementInterface;
use alcamo\rdf_literal_serializer\SerializerInterface;
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
             *  $tlvDataElement is constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "tag '{$this->tag_}' is constructed" ]
            );
        }

        $this->dataElement_->getDatatype()->throwUnlessEqualToOrDerivedFrom(
            $serializer->getDatatype()->getXName()
        );

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
