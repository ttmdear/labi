<?php
namespace Labi;

use Webmozart\Assert\Assert as WebAssert;

class Assert extends WebAssert
{
    public static function keysExist($array, $keys, $message = '')
    {
        $ok = true;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                $ok = false;
                break;
            }
        }

        if (!$ok) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Array do not have need keys %s.',
                implode(',', $keys)
            ));
        }
    }
}
