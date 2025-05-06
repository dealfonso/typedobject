<?php
namespace ddn\typedobject;

require_once("TypedObject.php");

abstract class EnumValues {
    /**
     * The list of values for this enum.
     * @var array
     */
    const VALUES = [];

    /**
     * Returns true if the value is valid for this enum.
     * @param mixed $value the value to check
     * @return bool true if the value is valid for this enum
     */
    public static function is_valid($value) : bool {
        return in_array($value, static::VALUES, true);
    }

    /**
     * Returns the list of values for this enum, where the first one is the default value.
     * @return array the list of values for this enum
     */
    public static function values() : array {
        return static::VALUES;
    }

    // public static function fromValue($value) {
    //     if (!static::is_valid($value)) {
    //         throw new \TypeError("Invalid value for enum " . static::class . ": $value");
    //     }
    //     return $value;
    // }
}