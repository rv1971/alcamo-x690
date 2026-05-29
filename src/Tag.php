<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\exception\SyntaxError;

/**
 * @namespace alcamo::x690
 *
 * @brief X.690 encoding
 *
 * @sa [X.690 ASN.1 encoding rules](https://www.itu.int/rec/T-REC-X.690-202102-I/en)
 */

/**
 * @brief TLV tag
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-03-01
 */
class Tag extends ImmutableBinaryString
{
    /// Create from binary string
    public function __construct(string $data)
    {
        parent::__construct($data);

        /** @throw alcamo::exception::SyntaxError if not correctly encoded. */
        if (isset($this[1])) {
            if (($this[0] & 0x1F) != 0x1F) {
                throw (new SyntaxError())->setMessageContext(
                    [
                        'inData' => $this,
                        'extraMessage' => 'short form longer than 1 byte'
                    ]
                );
            }

            if ($this[1] == 0x80) {
                throw (new SyntaxError())->setMessageContext(
                    [
                        'inData' => $this,
                        'extraMessage' => 'long form not correctly encoded'
                    ]
                );
            }

            for ($i = 1; $i < count($this) - 1; $i++) {
                if (!($this[$i] & 0x80)) {
                    throw (new SyntaxError())->setMessageContext(
                        [
                            'inData' => $this,
                            'extraMessage' => 'long form not correctly encoded'
                        ]
                    );
                }
            }

            if ($this[$i] & 0x80) {
                throw (new SyntaxError())->setMessageContext(
                    [
                        'inData' => $this,
                        'extraMessage' => 'long form not correctly encoded'
                    ]
                );
            }
        } else {
            if (($this[0] & 0x1F) == 0x1F) {
                throw (new SyntaxError())->setMessageContext(
                    [
                        'inData' => $this,
                        'extraMessage' => 'long form only 1 byte long'
                    ]
                );
            }
        }
    }

    /// Whether end of content tag
    public function isEoc(): bool
    {
        return $this->data_ == "\x00";
    }

    /// Whether constructed tag
    public function isConstructed(): bool
    {
        return $this[0] & 0x20;
    }
}
