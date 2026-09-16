<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\data_element\DeInstanceInterface;
use alcamo\exception\InvalidType;

/**
 * @brief Instance of a constructed TLV data element
 *
 * @note Since this class contains raw *content* but not the raw *length*,
 * serializing a TLV data element always uses the definite form of length
 * encoding, even if the data element was deserialized from data with the
 * indefinite form. In this case the result of deserializing and subsequent
 * serializing does not exactly reproduce the original data.
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-05-06
 */
class ConstructedTlvDeInstance extends AbstractTlvDeInstance
{
    public static function newFromChildren(
        ConstructedTlvDataElement $tlvDataElement,
        iterable $children,
        ?int $length = null
    ): self {
        return new static(
            $tlvDataElement,
            new ImmutableBinaryString(
                str_pad(
                    (new TlvSerializer())->serializeTlvDeInstances($children),
                    $length,
                    "\x00"
                )
            ),
            $children
        );
    }

    private $children_; ///< array of AbstractTlvDeInstance

    public function __construct(
        ConstructedTlvDataElement $tlvDataElement,
        ImmutableBinaryString $rawContent,
        iterable $children
    ) {
        $this->children_ = [];

        foreach ($children as $key => $deInstance) {
            /** @throw alcamo::exception::InvalidType if an item in $children
             *  is not an AbstractTlvDeInstance object. */
            InvalidType::throwIfNotInstanceOf(
                $deInstance,
                AbstractTlvDeInstance::class,
                null,
                [ 'forKey' => $key ]
            );

            $this->children_[$key] = $deInstance;
        }

        parent::__construct($tlvDataElement, $rawContent);
    }

    public function getChildren(): array
    {
        return $this->children_;
    }

    /**
     * @copybrief alcamo::x690::AbstractTlvDeInstance::equals()
     *
     * This is the case if both are instances of this class AND belong to the
     * same TLV data element AND the raw contents are equal AND all children
     * are considered equal.
     */
    public function equals(object $deInstance): bool
    {
        if (!($deInstance instanceof self)) {
            return false;
        }

        if (
            $this->getDataElement() != $deInstance->getDataElement()
            || $this->getRawContent() != $deInstance->getRawContent()
        ) {
            return false;
        }

        reset($deInstance->children_);

        foreach ($this->children_ as $child) {
            if (!$child->equals(current($deInstance->children_))) {
                return false;
            }

            next($deInstance->children_);
        }

        return true;
    }
}
