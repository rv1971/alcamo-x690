<?php

namespace alcamo\x690;

use alcamo\data_element\{DataElementInterface, SerializerInterface};
use alcamo\exception\DataValidationFailed;

/**
 * @brief Primitive TLV data element
 *
 * @date Last reviewed 2026-05-14
 */
class PrimitiveTlvDataElement extends AbstractTlvDataElement
{
    private $serializer_;  ///< SerializerInterface
    private $format_;      ///< string

    public function __construct(
        Tag $tag,
        DataElementInterface $dataElement,
        SerializerInterface $serializer,
        ?string $format = null
    ) {
        parent::__construct($tag, $dataElement);

        if ($this->tag_->isConstructed()) {
            /** @throw alcamo::exception::DataValidationFailed if tag in
             *  $tlvDataElement is not constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "{$this->tag_} is constructed" ]
            );
        }

        if (
            !$this->dataElement_->getDatatype()->isEqualToOrDerivedFrom(
                $serializer->getDatatype()->getXName()
            )
        ) {
            /** @todo throw alcamo::exception::DataValidationFailed if the
             *  data element datatype it not equal to or derived from the
             *  serializer datatype. */
            throw (new DataValidationFailed())->setMessageContext(
                [
                    'extraMessage' => 'the data element datatype '
                        . $this->dataElement_->getDatatype()->getXName()
                        . ' is not equal to or derived from '
                        . 'the serializer datatype'
                        . $serializer->getDatatype()->getXName()
                ]
            );
        }

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

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer_;
    }

    public function getFormat(): string
    {
        return $this->format_;
    }
}
