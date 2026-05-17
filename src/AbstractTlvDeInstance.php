<?php

namespace alcamo\x690;

use alcamo\binary_data\BinaryString;
use alcamo\data_element\DataElementInterface;

/**
 * @brief Instance of a TLV data element
 *
 * @date Last reviewed 2026-05-05
 */
abstract class AbstractTlvDeInstance
{
    private $tlvDataElement_; ///< TlvDataElementInterface
    private $rawContent_;     ///< BinaryString

    protected function __construct(
        TlvDataElementInterface $tlvDataElement,
        BinaryString $rawContent
    ) {
        $this->tlvDataElement_ = $tlvDataElement;
        $this->rawContent_ = clone $rawContent;
    }

    public function __clone()
    {
        $this->tlvDataElement_ = clone $this->tlvDataElement_;
        $this->rawContent_ = clone $this->rawContent_;
    }

    public function getDataElement(): DataElementInterface
    {
        return $this->tlvDataElement_;
    }

    public function getLength(): int
    {
        return count($this->rawContent_);
    }

    public function getRawContent(): BinaryString
    {
        return $this->rawContent_;
    }
}
