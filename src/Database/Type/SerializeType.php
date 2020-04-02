<?php
declare(strict_types=1);

namespace EmailQueue\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\OptionalConvertInterface;

class SerializeType extends BaseType implements OptionalConvertInterface
{
    /**
     * Creates a PHP value from a stored representation
     *
     * @param mixed $value to unserialize
     * @param \Cake\Database\DriverInterface $driver database driver
     * @return mixed|null|string|void
     */
    public function toPHP($value, DriverInterface $driver)
    {
        if ($value === null) {
            return null;
        }

        return unserialize($value);
    }

    /**
     * Generates a storable representation of a value
     *
     * @param mixed $value to serialize
     * @param \Cake\Database\Driver $driver database driver
     * @return null|string
     */
    public function toDatabase($value, DriverInterface $driver): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        }

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
    public function requiresToPhpCast(): bool
    {
        return true;
    }
}
