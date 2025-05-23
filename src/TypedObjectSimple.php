<?php
namespace ddn\typedobject;

require_once("TypedObject.php");

class TypedObjectSimple extends TypedObject {
    protected static function _introspect_attributes() {
        // We are building the definition of attributes for the preceding classes, so that we know the definition for
        //  the attributes of the this class coming from the preceding classes
        $definition_of_attributes_for_preceding_classes = [];
        $parent_class = get_called_class();
        while ($parent_class !== false) {
            if (!empty(static::$_attributeDefinition[$parent_class])) {
                $definition_of_attributes_for_preceding_classes = array_merge(static::$_attributeDefinition[$parent_class], $definition_of_attributes_for_preceding_classes);
            }
            $parent_class = get_parent_class($parent_class);
        }

        // Now we are building the attribute definition for this specific class, starting with the definition of the preceding classes
        $definition_of_attributes = [ ...$definition_of_attributes_for_preceding_classes ];

        $class = new \ReflectionClass(static::class);

        foreach ($class->getProperties() as $property) {
            // We are skipping static attributes, because these are not part of the object, but of the class
            if ($property->isStatic()) {
                continue;
            }
            // We are skipping private and protected attributes, so that we can use that scope for internal purposes and not for exporting them when converting to a JSON object
            if ($property->isPrivate() || $property->isProtected()) {
                continue;
            }
            $name = $property->getName();
            $type = $property->getType();
            if ($type == null) {
                $type_definition = TypeDefinition::fromType('mixed', true);
            } else {
                $type_name = $type->getName();
                $is_nullable = $type->allowsNull();
                $subtype = null;
                switch ($type_name) {
                    case 'mixed':
                        $is_nullable = true;
                        break;
                    case 'object':
                        $type_name = 'dict';
                        $subtype = TypeDefinition::fromType("mixed", true);
                        break;
                }
                // TODO: this generates an error "Value of type string returned from User2::__get() must be compatible with unset property User2::$type of type UserType"
                //  and this is because PHP checks that the RHS of the assignment is compatible with the LHS.
                //  To solve this, we would need to re-consider the enum type definition. For example, we could add a "value" property to the enum class and use that value
                //    as the value of the attribute, and then implement the toObject method for the EnumClass to return the value of the property. Then we'll be able to 
                //    change the definition of the enum attributes to accept either "enum[ValuesClass]" or directly the "ValuesClass" derived class. In this case, we would
                //    also be able to use it for the simple object definition by casting the type to the enum class.
                //  This may be a break change, so we need to consider this carefully.

                // We are checking if the type is a subclass of EnumValues, in which case we need to use TypeDefinitionEnum
                if (is_a($type_name, "ddn\\typedobject\\EnumValues", true)) {
                    // throw new \Exception("EnumValues are not supported as types for TypedObjectSimple");
                    $type_definition = TypeDefinitionEnum::fromType('enum', $is_nullable, $type_name);
                } else {
                    $type_definition = TypeDefinition::fromType($type_name, $is_nullable, $subtype);
                }
            }
            
            // Now we are detecting wether there are any attribute that shadows the definition of previous classes
            //  this is not allowed because it would be confusing. 
            // The only case in which we allow shadowing is when the attribute only changes the default value of the 
            //  attribute. In any other case, we'll throw an exception
            //
            // (*) if the definition is the same, we'll not complain about it
            if (isset($definition_of_attributes_for_preceding_classes[$name])) {
                if (!$definition_of_attributes_for_preceding_classes[$name]->equals($type_definition)) {
                    throw new \Exception("Attribute $name shadows the definition of previous classes in class ".get_called_class());
                }
            }

            // Store the attribute definition
            $definition_of_attributes[$name] = $type_definition;            
        }

        static::$_attributeDefinition[static::class] = $definition_of_attributes;
    }
}