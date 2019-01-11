<?php
namespace EmailQueue\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type\StringType;

class JsonType extends StringType
{
    /**
     * Decodes a JSON string
     *
     * @param mixed $value json string to decode
     * @param Driver $driver database driver
     * @return mixed|null|string|void
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return;
        }

        return json_decode($value, true);
    }

    /**
     * Marshal - Decodes a JSON string
     *
     * @param mixed $value json string to decode
     * @return mixed|null|string
     */
    public function marshal($value)
    {
        if (is_array($value) || $value === null) {
            return $value;
        }

        return json_decode($value, true);
    }

    /**
     * Returns the JSON representation of a value
     *
     * @param mixed $value string or object to encode
     * @param Driver $driver database driver
     * @return null|string
     */
    public function toDatabase($value, Driver $driver)
    {
        return json_encode($value);
    }

    /**
     * Returns whether the cast to PHP is required to be invoked
     *
     * @return bool always true
     */
    public function requiresToPhpCast()
    {
        return true;
    }
}
