<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\data_element\DataElementInterface;

/**
 * @brief Instance of a TLV data element
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-05-05
 */
abstract class AbstractTlvDeInstance
{
    private $tlvDataElement_; ///< TlvDataElementInterface
    private $rawContent_;     ///< ImmutableBinaryString

    protected function __construct(
        TlvDataElementInterface $tlvDataElement,
        ImmutableBinaryString $rawContent
    ) {
        $this->tlvDataElement_ = $tlvDataElement;
        $this->rawContent_ = $rawContent;
    }

    public function getDataElement(): DataElementInterface
    {
        return $this->tlvDataElement_;
    }

    public function getLength(): int
    {
        return count($this->rawContent_);
    }

    public function getRawContent(): ImmutableBinaryString
    {
        return $this->rawContent_;
    }
}
