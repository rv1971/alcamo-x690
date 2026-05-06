<?php

namespace alcamo\x690;

use alcamo\exception\DataValidationFailed;
use alcamo\data_element\DeInstanceInterface;

/**
 * @brief Instance of a primitive TLV data element
 *
 * @date Last reviewed 2026-05-05
 */
class PrimitiveTlvDeInstance extends AbstractTlvDeInstance implements
    DeInstanceInterface
{
    public static function newFromRawContent(
        TlvDataElement $tlvDataElement,
        BinaryString $rawContent
    ): self {
        return new static(
            $tlvDataElement,
            $rawContent,
            $tlvDataElement->getSerializer()
                ->deserialize($rawContent->getValue())
        );
    }

    public static function newFromLiteral(
        TlvDataElement $tlvDataElement,
        LiteralInterface $literal
    ): self {
        return new static(
            $tlvDataElement,
            new BinaryString(
                $tlvDataElement->getSerializer()->serialize($literal)
            ),
            $literal
        );
    }

    private $literal_; ///< LiteralInterface

    protected function __construct(
        TlvDataElement $tlvDataElement,
        BinaryString $rawContent,
        LiteralInterface $literal
    ) {
        if ($tlvDataElement->getTag()->isConstructed()) {
            /** @throw alcamo::exception::DataValidationFailed if tag in
             *  $tlvDataElement is not constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "$tag is constructed" ]
            );
        }

        parent::__construct($tlvDataElement, $rawContent);

        $this->literal_ = $literal;
    }

    public function getLiteral(): LiteralInterface
    {
        return $this->literal_;
    }
}
