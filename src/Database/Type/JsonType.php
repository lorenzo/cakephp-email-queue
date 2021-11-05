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
        return $this->decodeJson($value);
    }

    /**
     * Marshal - Decodes a JSON string
     *
     * @param mixed $value json string to decode
     * @return mixed|null|string
     */
    public function marshal($value)
    {
        return $this->decodeJson($value);
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

    /**
     * Returns the given value as an array (if it is json or already an array)
     * or as a string (if it is already a string)
     *
     * @param array|string|null $value json string, array or string to decode
     * @return array|string|null  depending on the input, see description
     */
    private function decodeJson($value)
    {
        if (is_array($value) || $value === null) {
            return $value;
        }

        $jsonDecode = json_decode($value, true);

        // check, if the value is null after json_decode to handle plain strings
        if ($jsonDecode === null) {
            return $value;
        }

        return $jsonDecode;
    }
}
