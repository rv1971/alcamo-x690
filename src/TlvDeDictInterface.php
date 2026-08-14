<?php

namespace alcamo\x690;

use alcamo\xml\NamespaceConstantsInterface;

/**
 * @brief Dictionary for TLV data elements
 *
 * @date Last reviewed 2026-05-06
 */
interface TlvDeDictInterface extends NamespaceConstantsInterface
{
    /**
     * @param $tag Tag|string Tag or hexadecimal string representation of a tag.
     *
     * @param $parent Tag|string Parent tag or string. If the tag to look up
     * is top-level within the TLV data, a parent string might indicate the
     * context of the whole TLV data block.
     *
     * @return Always a TLV data element, even if the tag is unknown for the
     * given parent, in which case the data element may contain little
     * information but can still be handled.
     */
    public function lookup($tag, $parent = null): TlvDataElementInterface;

    /// Get the End-of-content data element instance
    public function getEocDeInstance(): PrimitiveTlvDeInstance;

    public function createPrimitiveTlvDeInstance(
        $value,
        $tag,
        $parent = null
    ): PrimitiveTlvDeInstance;
}
