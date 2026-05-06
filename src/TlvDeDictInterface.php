<?php

namespace alcamo\x690;

/**
 * @brief Dictionary for TLV data elements
 *
 * @date Last reviewed 2026-05-06
 */
interface TlvDeDictInterface
{
    /**
     * @param $tag Tag|string Tag or hexadecimal string representation of a tag.
     *
     * @param $parent Tag|string Parent tag or string. If the tag to look up
     * is top-level within the TLV data, a parent string might indicate the
     * context of the whole TLV data block.
     */
    public function lookup($tag, $parent = null): TlvDataElementInterface;
}
