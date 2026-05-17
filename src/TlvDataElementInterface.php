<?php

namespace alcamo\x690;

use alcamo\data_element\DataElementInterface;

/**
 * @brief TLV data element
 *
 * @date Last reviewed 2026-05-14
 */
interface TlvDataElementInterface extends DataElementInterface
{
    public function getTag(): Tag;

    public function getFormat(): string;
}
