<?php
declare(strict_types=1);

namespace EmailQueue\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\OptionalConvertInterface;

class JsonType extends BaseType implements OptionalConvertInterface
{
    /**
     * Decodes a JSON string
     *
     * @param mixed $value json string to decode
     * @param \Cake\Database\DriverInterface $driver database driver
     * @return mixed|null|string|void
     */
    public function toPHP($value, DriverInterface $driver)
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
     * @param \Cake\Database\DriverInterface $driver database driver
     * @return null|string
     */
    public function toDatabase($value, DriverInterface $driver): ?string
    {
        return json_encode($value);
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
