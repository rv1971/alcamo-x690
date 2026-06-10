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

    public function equals(AbstractTlvDeInstance $tlvDeInstance): bool
    {
        if (!($tlvDeInstance instanceof self)) {
            return false;
        }

        return $this->getDataElement() == $tlvDeInstance->getDataElement()
            && $this->getRawContent() == $tlvDeInstance->getRawContent()
            && $this->literal_ = $tlvDeInstance->literal_;
    }
}
