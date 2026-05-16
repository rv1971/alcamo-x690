<?php

namespace alcamo\x690;

use alcamo\data_element\DataElementInterface;
use alcamo\xml\NamespaceConstantsInterface;

/**
 * @brief TLV data element
 *
 * @date Last reviewed 2026-05-14
 */
interface TlvDataElementInterface extends
    DataElementInterface,
    NamespaceConstantsInterface
{
    public function getTag(): Tag;

    public function getFormat(): string;
}
