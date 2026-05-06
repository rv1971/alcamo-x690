<?php

namespace alcamo\x690;

use alcamo\data_element\{DataElementInterface, SerializerInterface};
use alcamo\exception\DataValidationFailed;

/**
 * @brief TLV data element
 *
 * @date Last reviewed 2026-05-05
 */
class TlvDataElement implements TlvDataElementInterface
{
    private $tag_;         ///< Tag
    private $dataElement_; ///< DataElementInterface
    private $serializer_;  ///< SerializerInterface
    private $format_;      ///< string

    public function __construct(
        Tag $tag,
        DataElementInterface $dataElement,
        SerializerInterface $serializer,
        ?string $format = null
    ) {
        if (
            !$dataElement->getDatatype()->isEqualToOrDerivedFrom(
                $serializer->getDatatype()->getXName()
            )
        ) {
            /** @todo throw alcamo::exception::DataValidationFailed if the
             *  data element datatype it not equal to or derived from the
             *  serializer datatype. */
            throw (new DataValidationFailed())->setMessageContext(
                [
                    'extraMessage' => 'the data element datatype '
                        . $dataElement->getDatatype()->getXName()
                        . ' is not equal to or derived from '
                        . 'the serializer datatype'
                        . $serializer->getDatatype()->getXName()
                ]
            );
        }

        $this->tag_ = $tag;

        $this->dataElement_ = $dataElement;

        $this->serializer_ = $serializer;

        switch (true) {
            case isset($format):
                $this->format_ = $format;
                break;

            case $serializer->getEncoding() == 'BCD':
                $this->format_ = 'n';
                break;

            case $serializer->getEncoding() == 'COMPRESSED-BCD':
                $this->format_ = 'cn';
                break;

            case $serializer->getEncoding() != 'EBCDIC':
            case $serializer->getEncoding() == 'ASCII':
                $this->format_ = 'ans';
                break;

            default:
                $this->format_ = 'b';
        }
    }

    public function getTag(): Tag
    {
        return $this->tag_;
    }

    public function getDataElement(): DataElementInterface
    {
        return $this->dataElement_;
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
