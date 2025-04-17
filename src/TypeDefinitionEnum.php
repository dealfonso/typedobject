<?php

namespace ddn\typedobject;

require_once("EnumValues.php");

class TypeDefinitionEnum extends TypeDefinition {
    protected $values_class = "";

    /**
     * Creates a TypeDefinitionEnum using the fromType factory method, acapted to the enum type. This is needed because instead of
     *  passing a _subtype_, we need a EnumValues class.
     * @param string $type the type of the object (it must be "enum")
     * @param bool $nullable true if the type is nullable
     * @param string $values_class the class name of the EnumValues class
     * @return TypeDefinitionEnum a TypeDefinitionEnum object
     */
    public static function fromType(string $type, bool $nullable = false, $values_class = null) : TypeDefinition {
        if ($type === 'enum') {
            if ($values_class === null) {
                throw new \Exception("TypeDefinitionEnum::fromType() expects a values class, null given");
            }
            if (!is_a($values_class, "ddn\\typedobject\\EnumValues", true)) {
                throw new \Exception("TypeDefinitionEnum::fromType() expects a subclass of EnumValues, '$values_class' given");
            }
            $instance = new self([
                'type' => $type,
                'nullable' => $nullable,
                'subtype' => null,
            ]);

            $instance->values_class = $values_class;
            return $instance;
        } else {
            throw new \Exception("TypeDefinitionEnum::fromType() expects 'enum' as type, '$type' given");
        }
    }

    /**
     * Compares the current TypeDefinitionEnum with another TypeDefinitionEnum.
     * @param TypeDefinition $other the other TypeDefinitionEnum to compare with
     * @param bool $consider_default true if the default value should be considered
     * @return bool true if the two TypeDefinitionEnum are equal, false otherwise
     */
    public function equals(?TypeDefinition $other, bool $consider_default = false) : bool {
        if (is_a($other, "ddn\\typedobject\\TypeDefinitionEnum", true)) {
            if ($this->values_class !== $other->values_class) {
                return false;
            }
        } 
        return parent::equals($other, $consider_default);
    }

    /**
     * Returns the default value for this TypeDefinitionEnum, which is the first value in the list of values.
     * @return mixed the default value for this TypeDefinitionEnum
     * @throws \TypeError if the type is not nullable and the default value is null
     */
    public function default_value() {
        if ($this->nullable) {
            return null;
        }
        return $this->values_class::values()[0] ?? null;
    }

    /**
     * Parses the value and returns it as a valid value for this TypeDefinitionEnum.
     * @param mixed $value the value to parse
     * @return mixed the parsed value
     * @throws \TypeError if the value is not valid for this TypeDefinitionEnum
     */
    public function parse_value(mixed $value) : mixed {
        // The type is "enum"
        if ($value === null) {
            if (!$this->nullable) {
                throw new \TypeError("not nullable");
            }
            return null;
        }
        if ($this->values_class::is_valid($value)) {
            return $value;
        }
        throw new \TypeError(sprintf("expected a value from enum[%s], but received \"%s\" (%s)", $this->values_class, $value, gettype($value)));
    }

    /**
     * Returns the string representation of this TypeDefinitionEnum.
     * @return string the string representation of this TypeDefinitionEnum
     */
    public function __toString() : string {
        $result = "enum";
        if ($this->nullable) {
            $result = '?' . $result;
        }
        $result .= '[' . $this->values_class . ']';
        return $result;
    }

    /**
     * Converts the value to a value to be exported as JSON.
     * @param mixed $value the value to convert
     * @return mixed the converted value
     * @throws \TypeError if the value is not valid for this TypeDefinitionEnum
     */
    public function convert_value($value) {
        if (!$this->values_class::is_valid($value)) {
            throw new \TypeError(sprintf("expected a value from enum[%s], but received \"%s\" (%s)", $this->values_class, $value, gettype($value)));
        }
        return $value;
    }
}

