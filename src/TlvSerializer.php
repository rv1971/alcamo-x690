<?php

namespace alcamo\x690;

/**
 * @brief Serializer for TLV data element instances
 *
 * @date Last reviewed 2026-05-06
 */
class TlvSerializer
{
    public function serializeLength(int $length): string
    {
        switch (true) {
            /** Serialize -1 (indefinite length) as 0x80. */
            case $length == -1:
                return "\x80";

                /** Serialize a length up to 0x7F using the short form. */
            case $length <= 0x7F:
                return chr($length);

            default:
                /** Serialize larger values using the long form. Lengthes up
                 *  to 4GiB - 1 are supported. */
                $lengthBytes = ltrim(pack('N', $length), "\x00");

                return chr(0x80 + strlen($lengthBytes)) . $lengthBytes;
        }
    }

    public function serializeTlvDeInstance(
        AbstractTlvDeInstance $deInstance
    ): string {
        return $deInstance->getDataElement()->getTag()->getData()
            . $this->serializeLength($deInstance->getLength())
            . $deInstance->getRawContent()->getData();
    }

    public function serializeTlvDeInstances(iterable $deInstances): string
    {
        $result = '';

        foreach ($deInstances as $key => $deInstance) {
            $result .= $this->serializeTlvDeInstance($deInstance);
        }

        return $result;
    }
}
