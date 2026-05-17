<?php

namespace alcamo\x690;

use alcamo\data_element\DataElementInterface;
use alcamo\dom\schema\component\SimpleTypeInterface;
use alcamo\rdfa\RdfaData;

/**
 * @brief TLV data element
 *
 * @date Last reviewed 2026-05-14
 */
abstract class AbstractTlvDataElement implements TlvDataElementInterface
{
    protected $tag_;         ///< Tag
    protected $dataElement_; ///< DataElementInterface

    public function __construct(Tag $tag, DataElementInterface $dataElement)
    {
        $this->tag_ = $tag;
        $this->dataElement_ = clone $dataElement;
    }

    public function __clone()
    {
        $this->dataElement_ = clone $this->dataElement_;
    }

    public function getTag(): Tag
    {
        return $this->tag_;
    }

    public function getDataElement(): DataElementInterface
    {
        return $this->dataElement_;
    }

    public function getDatatype(): SimpleTypeInterface
    {
        return $this->dataElement_->getDatatype();
    }

    public function getRdfaData(): ?RdfaData
    {
        return $this->dataElement_->getRdfaData();
    }

    public function getLabel($lang = null, ?int $flags = null): ?string
    {
        return $this->dataElement_->getLabel($lang, $flags)
            . ' (' . $this->getFormat() . ')';
    }
}
