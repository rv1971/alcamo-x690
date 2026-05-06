<?php

namespace alcamo\x690;

use alcamo\data_element\{DataElementInterface, SerializerInterface};

/**
 * @brief TLV data element
 *
 * @date Last reviewed 2026-05-05
 */
interface TlvDataElementInterface
{
    public function getTag(): Tag;

    public function getDataElement(): DataElementInterface;

    public function getSerializer(): SerializerInterface;

    public function getFormat(): string;
}
