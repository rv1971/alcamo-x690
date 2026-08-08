<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\collection\CollectionInterface;
use alcamo\data_element\{
    ConstructedDataElement,
    DeInstance,
    DeInstanceInterface
};
use alcamo\exception\ExceptionInterface;
use alcamo\rdf_literal\{AbstractConstructedLiteral, LiteralInterface};

/**
 * @brief Instance of a primitive TLV data element
 *
 * The factory methods and the constructor neither validate the literal nor
 * check that the literal data type is compatible with the data element
 * datatype.
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
        ?int $serializedLength = null
    ): self {
        return new static(
            $tlvDataElement,
            new ImmutableBinaryString(
                $tlvDataElement->getSerializer()
                    ->serialize($literal, $serializedLength)
            ),
            $literal
        );
    }

    public static function newFromRawContent(
        PrimitiveTlvDataElement $tlvDataElement,
        ImmutableBinaryString $rawContent
    ): self {
        try {
            return new static(
                $tlvDataElement,
                $rawContent,
                $tlvDataElement->getSerializer()
                    ->deserialize($rawContent->getData())
            );
        } catch (ExceptionInterface $e) {
            $e->addMessageContext(
                [
                    'tag' => (string)$tlvDataElement->getTag()
                ]
            );

            throw $e;
        }
    }

    private $literal_;          ///< LiteralInterface
    private $deInstance_;       ///< DeInstanceInterface
    private $children_ = false; ///< ?CollectionInterface

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

    public function hasChildren(): bool
    {
        return $this->getDataElement()
            ->getDataElement() instanceof ConstructedDataElement
            && $this->literal_ instanceof AbstractConstructedLiteral;
    }

    public function getChildren(): ?CollectionInterface
    {
        if ($this->children_ === false) {
            return $this->children_ = $this->hasChildren()
                ? static::createChildren(
                    $this->getDataElement()->getDataElement(),
                    $this->literal_
                )
                : null;
        }

        return $this->children_;
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
     *   both are equal in the sense of
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
