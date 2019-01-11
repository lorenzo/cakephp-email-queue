<?php
namespace EmailQueue\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type\StringType;

class SerializeType extends StringType
{
    /**
     * Creates a PHP value from a stored representation
     *
     * @param mixed $value to unserialize
     * @param Driver $driver database driver
     * @return mixed|null|string|void
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return;
        }

        return unserialize($value);
    }

    /**
     * Generates a storable representation of a value
     *
     * @param mixed $value to serialize
     * @param Driver $driver database driver
     * @return null|string
     */
    public function toDatabase($value, Driver $driver)
    {
        return serialize($value);
    }

    /**
     * Marshal - Return the value as is
     *
     * @param mixed $value php object
     * @return mixed|null|string
     */
    public function marshal($value)
    {
        return $value;
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
