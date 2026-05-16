<?php

namespace alcamo\x690;

use alcamo\exception\InvalidType;

/**
 * @brief Instance of a constructed TLV data element
 *
 * @note Since the TLV data element contains raw *content* but not the raw
 * *length*, serializing a TLV data element always uses the definite form of
 * length encoding, even if the data element was deserialized from data with
 * the indefinite form. In this case the result of deserializing and
 * subsequent serializing does not exactly reproduce the original data.
 *
 * @date Last reviewed 2026-05-06
 */
class ConstructedTlvDeInstance extends AbstractTlvDeInstance
{
    public static function newFromChildren(
        ConstructedTlvDataElement $tlvDataElement,
        iterable $children
    ): self {
        return new static(
            $tlvDataElement,
            $children,
            (new TlvSerializer())->serializeTlvDeInstances($children)
        );
    }

    private $children_; ///< array of AbstractTlvDeInstance

    public function __construct(
        ConstructedTlvDataElement $tlvDataElement,
        BinaryString $rawContent,
        iterable $children
    ) {
        $this->children_ = [];

        foreach ($children as $key => $deInstance) {
            if (!($deInstance instanceof AbstractTlvDeInstance)) {
                /** @throw alcamo::exception::InvalidType if an item in
                 *  $children is not an AbstractTlvDeInstance object. */
                throw (new InvalidType())->setMessageContext(
                    [
                        'value' => $deInstance,
                        'expectedOneOf' => AbstractTlvDeInstance::class,
                        'forKey' => $key
                    ]
                );
            }

            $this->children_[$key] = $deInstance;
        }

        parent::__construct($tlvDataElement, $rawContent);
    }

    public function getChildren(): array
    {
        return $this->children_;
    }
}
