<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\data_element\DeInstanceInterface;
use alcamo\rdf_literal\LiteralInterface;

/**
 * @brief Instance of a primitive TLV data element
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-05-05
 */
class PrimitiveTlvDeInstance extends AbstractTlvDeInstance implements
    DeInstanceInterface
{
    public static function newFromLiteral(
        PrimitiveTlvDataElement $tlvDataElement,
        LiteralInterface $literal,
        ?int $length = null
    ): self {
        return new static(
            $tlvDataElement,
            new ImmutableBinaryString(
                $tlvDataElement->getSerializer()->serialize($literal, $length)
            ),
            $literal
        );
    }

    public static function newFromRawContent(
        PrimitiveTlvDataElement $tlvDataElement,
        ImmutableBinaryString $rawContent
    ): self {
        return new static(
            $tlvDataElement,
            $rawContent,
            $tlvDataElement->getSerializer()
                ->deserialize($rawContent->getData())
        );
    }

    private $literal_; ///< LiteralInterface

    protected function __construct(
        PrimitiveTlvDataElement $tlvDataElement,
        ImmutableBinaryString $rawContent,
        LiteralInterface $literal
    ) {
        parent::__construct($tlvDataElement, $rawContent);

        $this->literal_ = $literal;
    }

    public function getLiteral(): LiteralInterface
    {
        return $this->literal_;
    }

    /**
     * @copybrief alcamo::x690::AbstractTlvDeInstance::equals()
     *
     * This is the case iff:
     * - Either both are instances of this class AND belong to the same TLV
     *   data element AND the raw contents and the contained literals are
     *   considered equal.
     * - Or $deInstance is an instance of
     *   alcamo::data_element::DeInstanceInterface but not of this class, AND
     *   bith are equal in the sense of
     *   alcamo::data_element::DeInstanceInterface::equals().
     */
    public function equals(object $deInstance): bool
    {
        switch (true) {
            case $deInstance instanceof self:
                return $this->getDataElement() == $deInstance->getDataElement()
                    && $this->getRawContent() == $deInstance->getRawContent()
                    && $this->literal_ = $deInstance->literal_;

            case $deInstance instanceof DeInstanceInterface:
                return $deInstance->equals($this);

            default:
                return false;
        }
    }
}
