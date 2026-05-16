<?php

namespace alcamo\x690;

use alcamo\data_element\DeInstanceInterface;
use alcamo\rdf_literal\LiteralInterface;

/**
 * @brief Instance of a primitive TLV data element
 *
 * @date Last reviewed 2026-05-05
 */
class PrimitiveTlvDeInstance extends AbstractTlvDeInstance implements
    DeInstanceInterface
{
    public static function newFromRawContent(
        PrimitiveTlvDataElement $tlvDataElement,
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
        PrimitiveTlvDataElement $tlvDataElement,
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
        PrimitiveTlvDataElement $tlvDataElement,
        BinaryString $rawContent,
        LiteralInterface $literal
    ) {
        parent::__construct($tlvDataElement, $rawContent);

        $this->literal_ = $literal;
    }

    public function getLiteral(): LiteralInterface
    {
        return $this->literal_;
    }
}
