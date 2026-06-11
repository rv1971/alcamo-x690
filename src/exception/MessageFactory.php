<?php

namespace alcamo\x690\exception;

use alcamo\exception\MessageFactory as MessageFactoryBase;

/**
 * @brief Exception message factory featuring the `inTag` placeholder
 *
 * @date Last reviewed 2026-06-08
 */
class MessageFactory extends MessageFactoryBase
{
    public const PLACEHOLDER_FLAGS = parent::PLACEHOLDER_FLAGS + [
        'inTag' => self::NO_QUOTE | self::NO_CLASS,
    ];

    public const MESSAGE_FRAGMENT_MAP = parent::MESSAGE_FRAGMENT_MAP + [
        'inTag' => " in tag '%s'"
    ];
}
