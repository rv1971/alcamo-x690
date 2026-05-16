<?php

namespace alcamo\x690;

use alcamo\data_element\DataElementInterface;
use alcamo\exception\{DataValidationFailed, InvalidType};

/**
 * @brief Constructed TLV data element
 *
 * @date Last reviewed 2026-05-07
 */
class ConstructedTlvDataElement extends AbstractTlvDataElement
{
    public function __construct(Tag $tag, DataElementInterface $dataElement)
    {
        parent::__construct($tag, $dataElement);

        if (!$this->tag_->getTag()->isConstructed()) {
            /** @throw alcamo::exception::DataValidationFailed if tag in
             *  $tlvDataElement is not constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "{$this->tag_} is primitive" ]
            );
        }

        if (
            $this->dataElement_->getDatatype()->getPrimitiveType()->getXName()
                != self::XSD_NS . ' hexBinary'
        ) {
            /** @throw alcamo::exception::InvalidType if datatype of data
             *  element is not equal to or derived from `xsd:hexBinary`. */
            throw (new InvalidType())->setMessageContext(
                [ 'expectedOneOf' => 'derived from xsd:hexBinary' ]
            );
        }
    }

    public function getFormat(): string
    {
        return 'b';
    }
}
