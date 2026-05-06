<?php

namespace alcamo\x690;

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
        $this->rawContent_ = $rawContent;
    }

    public function getTlvDataElement(): TlvDataElementInterface
    {
        return $this->tlvDataElement_;
    }

    public function getRawContent(): BinaryString
    {
        return $this->rawContent_;
    }
}
