<?php
namespace ddn\typedobject;

require_once("TypedObject.php");

class EnumValues {
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
}