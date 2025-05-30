# `TypedObject`

`TypedObject` is a PHP class that enables to parse JSON objects into PHP objects, and later getting them back into JSON objects.

At the end, this library can be used as a DTO (Data Transfer Object) backend for PHP. It is possible to define the types for the attributes of the objects, and the `TypedObject` base class will parse the JSON objects into the PHP objects, and will take care of checking the types of the attributes, or and/or converting them into the appropriate types when setting their values.

In this way, it makes it easier to implement the data model of the application, and to deal with the data that comes from other layers, such a REST API.

> The library also includes the classes `TypedDict` and `TypedList` that are used to deal with dictionaries and lists, respectively. These classes are used by `TypedObject` to manage the attributes that are defined as dictionaries or lists, but they can be used independently.

## Why `TypedObject`

I had to use an API from PHP, and that API returned me JSON Objects. So I needed to parse them into PHP objects that I was able to use in the app.

The workflow is
1. retrieve a JSON object definition
1. use `TypedObject` to parse the JSON definition
1. use the resulting objects in the application
1. get the JSON definition back
1. send the JSON definition to the API

## Use case

Let's take the following JSON example:

```json
{
    "id": 0,
    "name": "John Doe",
    "age": 42,
    "emails": [
        "my@email.com",
        "other@email.com"
    ],
    "address": {
        "street": "My street",
        "number": 42,
        "city": "My city",
        "country": "My country"
    }
}
```

Using `TypedObject`, I will be able to define my data model using the following classes:

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'int',
        'name' => 'string',
        'age' => 'int',
        'emails' => 'list[string]',
        'address' => '?Address',
    ];
}

class Address extends TypedObject {
    const ATTRIBUTES = [
        'street' => 'string',
        'number' => 'int',
        'city' => 'string',
        'country' => 'string',
    ];
}
```

And then add the following command:

```php
$user = User::fromObject(json_decode($json_text_definition));
```

The `TypedObject` class will carry out with parsing the content into objects, and we would be able to use its attributes as defined:

```php
echo($user->name);
```

During the usage of the objects, the library will take care of checking the types of the attributes, and will convert the values into the expected types. So that we can use the objects without worrying about the types of the attributes.

e.g.

```php
$user->age = 50;
$user->emails = [ "not@proceed.ing", "valid.email" ];
$user->address = [ 'street' => "My other street", 'number' => 10, 'city' => "Valencia", 'country' => "Spain" ];
```

And the result will be:

```json
{
    "id": 0,
    "name": "John Doe",
    "age": 50,
    "emails": [
        "not@proceed.ing",
        "valid.email"
    ],
    "address": {
        "street": "My other street",
        "number": 10,
        "city": "Valencia",
        "country": "Spain"
    }
}
```

> In this case, the library will convert the PHP list of emails into a `TypedList` object, and the PHP array of the address into an `Address` object without the intervention of the user. But also verifies that the age is an integer, and that the emails are strings, and the attributes in the address are those expected.

And even simpler, if we use the `TypedObjectSimple` class, we can define the classes like this:

```php
class Address extends TypedObjectSimple {
    public string $street;
    public int $number;
    public string $city;
    public string $country;
}
class User extends TypedObjectSimple {
    public int $id;
    public string $name;
    public int $age;
    public array $emails;
    public ?Address $address;
}
```

And we will be able to parse the JSON object in the same way:

```php
$user = User::fromObject(json_decode($json_text_definition));
```

... the rest of the example will work the same way.

> The `TypedObjectSimple` class is a simpler version of the `TypedObject` class, that uses the PHP 7.4 typed attributes to define the attributes of the class. It is simpler to use, but it has some limitations and lack of expressiveness when defining the data model.

### Extending the data model

The classes can also have methods that will make it easier to implement the application's data model. E.g. it would possible to define the class `User` like this:

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'int',
        'name' => 'string',
        'age' => 'int',
        'emails' => 'list[string]',
        'address' => '?Address',
    ];
    public function isAdult() {
        return $this->age >= 18;
    }
    public function addEmail($email) {
        $this->emails[] = $email;
    }
    public function setAddress($street, $number, $city, $country) {
        $this->address = new Address(street: $street, number: $number, city: $city, country: $country);
    }
}
```

The methods that use the attributes of the object will also be subject to the type checking, so that the values will be converted to the expected types. In this sense, the implementation is also type-safe.

## Features of `TypedObject`

1. **Type checking**: The attributes of the objects are typed, so that the library will check the types of the attributes when setting their values. If the value does not match the type, an exception will be raised. That means that the **types of the attributes of an object will be those that are expected** to be without the need to check them.
1. **Default values**: It is possible to set default values for the attributes, so that if the data comes from the API without some attributes, they will be initialized with the default values. This feature enables a more flexible way to **upgrade the data model of the application**, and keep the compatibility with the previous versions of the API.
1. **Nullable attributes**: It is possible to define attributes as nullable. That means that they can be set to `null`, but it also enables **more control over the values of the attributes**, as they can be set to `null` only if they are defined as nullable.
1. **Type conversion**: The library will convert the values to the expected types, even if they are not strictly the same type. This feature enables a more flexible way to deal with the data, as **the values will be converted to the expected types**.
1. **Inheritance**: The objects can inherit attributes from their parent classes. That means that the attributes of the parent class will be available in the child class. This feature enables a more **flexible way to define the data model** of the application.

## Installing

The library can be installed using composer:

```bash
composer require ddn/typedobject
```

And then it can be included in the PHP script:

```php
use ddn\typedobject\TypedObject;

include('vendor/autoload.php');

// The code goes here
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'int',
        'name' => 'string',
        ...
    ];
}
```

## Using `TypedObject`

The idea of the `TypedObject` class is to use it to parse _json_ data into objects. So that these objects may contain other methods that will help to implement the data model of the application.

When the json object (or array) is parsed, its content are recursively parsed according to the types defined in the `ATTRIBUTES` constant. If the data is not valid, because it does not contain the expected values, an exception is thrown. 

To use _TypedObject_ one must subclass `TypedObject` and define the `ATTRIBUTES` constant for that class so that it defines the attributes expected for the objects of that class, along with the type of each one.
 
### Defining the types for the attributes

The `ATTRIBUTES` constant is an associative array where the keys are the _name for each attribute_,  and the values are the _type for each attribute_.
 
The possible types can be:
- `int`: int number
- `float`: floating point number
- `string`: string
- `bool`: boolean
- `list[type]`: list of objects of type _type_.
- `dict[type]`: dictionary of objects of type _type_. The keys for each entry of the dictionary is converted to strings.
- `enum[<EnumValues derived class>]`: a enumeration of possible values for the attribute. The values must be a subclass of `EnumValues` class.
- `<TypedObject derived class>`: is a class name which must be a subclass of `TypedObject`.
- `mixed`: any type is allowed for the attribute. This includes _null_ values. When using this type, if assigned an array or an object, it will be converted to a `TypedArray` or a `TypedObject` object, respectively.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'int',
        'name' => 'string',
        'age' => 'int',
        'emails' => 'list[string]',
        'address' => '?Address',
    ];
}
```

Then it is possible to instantiate the object using the static method `fromArray` or `fromObject`, or directly using the constructor:

```php
$json_text_definition = '{
    "id": 0,
    "name": "John Doe",
    "age": 42,
    "emails": [],
    "address": null
}';
$user = User::fromObject(json_decode($json_text_definition));
$user = User::fromArray(json_decode($json_text_definition, true));
$user = new User(id: 0, name: "John Doe", age: 42, emails: [], address: null);  // PHP 8 and over
$user = new User([ "id" => 0, "name" => "John Doe", "age" => 42, "emails" => [], "address" => null ]);  // PHP 7
```

Alternatively, it is possible to not to specify the type for any of the attributes. In that case, the attribute will be considered to be of type `mixed`.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'int',
        'name' => 'string',
        'age',
        'emails',
        'address',
    ];
}
```

### Using typed attributes and the `TypedObjectSimple` class

The preferred method to use the `TypedObject` library is by using the `TypedObject` class, and defining the data model using the constant `ATTRIBUTES`, and then do not define the attributes in the class definition (or at least do not define them as PHP 7.4 typed attributes) and let the library to take care of them.

But there is also a simpler version of the class, the `TypedObjectSimple` class, that avoids the use of the `ATTRIBUTES` constant, and uses the PHP 7.4 typed attributes to define the attributes of the class.

e.g. 

```php
class Address extends TypedObjectSimple {
    public string $street;
    public int $number;
    public string $city;
    public string $country;
}
class User extends TypedObjectSimple {
    public int $id;
    public string $name;
    public int $age;
    public array $emails;
    public ?Address $address;
}
```

In this case, if we have the a JSON fragment that accomplishes the data model, we can use the following code to get an instance of the `User` class:

```php
$json_text_definition = '{
    "id": 0,
    "name": "John Doe",
    "age": 42,
    "emails": [],
    "address": {
        "street": "My street",
        "number": 40,
        "city": "Valencia",
        "country": "Spain"
    }
}';
$user = User::fromObject(json_decode($json_text_definition));
```

The only advise is that **every public attribute in the class is considered to be part of the data model**, and so it will be parsed when creating the object. So, if the class has attributes that are not part of the data model, they must be defined as private or protected.

#### Notes on `TypedObjectSimple`

This class is simpler to use, but it has some limitations:
- It does not support the definition of default values for the attributes.
- It lacks of expressiveness when defining the data model, as it does not support the definition of the types for lists and dictionaries (e.g. `list[string]` or `dict[Address]`).
- The `array` type is different from a `list[mixed]` attribute so it does not have all of the features of `TypedList`.

  - When retrieving the arrays, the library will make its best by visiting each element in the array to try to convert the objects that are derived from `TypedObject`. But we cannot guarantee that the values in the array are properly serialized, in particular, when converting the object back to a JSON object.
  - The type of the values is not controlled by the library, and so some features such as type checking or type conversion will not be available for them. 
  - The contents of the array are not controlled by the library, so it may contain any type of value that maybe is not properly converted when building the JSON object. 

Anyway, if your data model is simple and you do not need to define the types for the values of `lists` or `dicts`, the `TypedObjectSimple` class may be a good choice.

### Default values for the attributes

It is possible to set default values for the attributes. This is done either in the attribute definition or in the class definition.

e.g. in the attribute definition:

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => [ 'string', 'generate_id' ],
        'name' => [ 'string', 'John Doe' ],
    ];
    function generate_id() {
        return uniqid();
    }
}
```

In this example, the attribute _name_ will be initialized to `John Doe` if not set. In case that the _default value_ is either a function or a method in the class, the default value will be the result of the call to that function or method. This is the case of the attribute _id_ in the example, which will be initialized to the result of the method `generate_id`.

e.g. in the class definition:

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'id' => 'string',
        'name' => 'string',
    ];
    public $name = "John Doe";
    public $id = "generate_id";
    function generate_id() {
        return uniqid();
    }
}
```

This is an equivalent way to set the default values for the attributes. In this case, the default value is set in the class definition by means of setting the value of the corresponding properties, and it is used when the attribute is not set when creating the object.

One can think that an uninitialized attribute may have a default value, as in non typed attributes. But in the case of `TypedObject`, an uninitialized attribute is not considered to have a default value, as they are typed attributes and it observes [Typed Properties in PHP 7.4](https://php.watch/versions/7.4/typed-properties) behavior.

If wanted to control this behavior, we can use the constant `ddn\typedobject\USE_DEFAULT_VALUES`. If set to `true`, the uninitialized attributes that do not have a default value will be initialized with the following default values:
- int: 0
- float: 0.0
- string: ""
- bool: false
- list[type]: []
- dict[type]: []
- enum[\<EnumValues derived class\>]: first value of the list of possible values.
- object: null
- mixed: null
- array: [] (only available for `TypedObjectSimple`)

> The default value for `ddn\typedobject\USE_DEFAULT_VALUES` is `false`, so the uninitialized attributes will not have a default value.

### Uninitialized attributes

If an attribute is not initialized (either because its value is set or it has a default value), it cannot be retrieved. So, if we try to get its value, it will raise an exception.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
    ];
}
$user = new User();
echo($user->name);
```

This will raise an exception because the attribute _name_ has not been initialized.

To check whether an attribute has been initialized or not, the function `isset` can be used.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
    ];
}
$user = new User();
if (!isset($user->name)) {
    echo "Name has not been set";
}
echo $user->name??"Name has not been set";
```

The use of the uninitialized state can be controlled by the constant `ddn\typedobject\USE_UNINITIALIZED_STATE`. If it is set to `true`, the `TypedObject` will consider the uninitialized state as a valid state for the attributes. If so, an attribute may be initialized later than the object creation.

e.g.

```php
define('ddn\\typedobject\\USE_UNINITIALIZED_STATE', true);
include('vendor/autoload.php');
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
    ];
}
$user = new User();
$user->name = "John Doe";
echo($user->name);
```

In this example, if we set the constant `ddn\typedobject\USE_UNINITIALIZED_STATE` to `true`, the output will be `John Doe`. Otherwise, it will raise an exception when trying to instantiate the object (`$user = new User()`) because the attribute _name_ has not been initialized when instantiating the object, and it does not have a default value.

If `ddn\typedobject\USE_UNINITIALIZED_STATE` is set to `false`, in case that an attribute is not initialized during the creation of the object, and it has not a default value, an exception will be raised.

e.g. 

```php
define('ddn\\typedobject\\USE_UNINITIALIZED_STATE', false);
include('vendor/autoload.php');
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
    ];
}
$user = new User();
```

In this example, an exception will be raised because the attribute _name_ has not been initialized and it does not have a default value.

To check if the object is fully initialized (i.e has all its attributes initialized), the function `is_initialized` can be used, and to get the list of the attributes that have not been initialized, the function `get_uninitialized_attributes` can be used.

```php
public function is_initialized() : bool {...}
public function get_uninitialized_attributes() : array {...}
```

> The default value for `ddn\typedobject\USE_UNINITIALIZED_STATE` is `true`, so not initializating an attribute will not raise an exception.

### Nullable attributes

To be able to assign the `null` value to an attribute, it needs to be defined as nullable. To mark a property can be null, prefix its type with a question mark, e.g: `?string`.

e.g. 

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
        'age' => '?int',
    ];
}
$user = new User();
$user->age = null;
```

In this example, the attribute _age_ is defined as nullable, so it is possible to assign `null` to it. If the attribute is not defined as nullable, assigning `null` to it will raise an exception of type `TypeError`.

It may be weird that an uninitialized nullable attribute is not considered to be `null`. But as of [Typed Properties in PHP 7.4](https://php.watch/versions/7.4/typed-properties), the uninitialized state is different from setting it to be `null`.

To control the behavior of the uninitialized nullable attributes, the constant `ddn\typedobject\UNINITIALIZED_NULLABLE_IS_NULL` can be set to `true`. If so, the uninitialized nullable attributes will be considered to be `null` as a default. Otherwise, they will be considered to be uninitialized.

> While PHP considers the uninitialized state of a nullable attribute to not to be null, the `TypedObject` library sets constant `ddn\typedobject\UNINITIALIZED_NULLABLE_IS_NULL` is set to `true` by default, as I found it more intuitive and code-friendly.

### Type checking

The `TypedObject` class will check the types of the attributes when setting their values. If the value does not match the type, an exception will be raised.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
        'age' => 'int',
    ];
}
$user = new User();
$user->name = 42;
```

In this example, an exception will be raised because the attribute _name_ is defined as a string, and we are trying to assign an integer to it.

To control this behavior, the constant `ddn\typedobject\STRICT_TYPE_CHECKING` can be set to `false`. If that case, some type conversions will be done when setting the values of the attributes:

- `int` to `float`: the integer will be converted to a float.
- `int` to `string`: the integer will be converted to a string.
- `string` to `int`: the string will be converted to an integer if it is a valid integer.
- `string` to `float`: the string will be converted to a float if it is a valid float.
- `string` to `bool`: the string will be converted to a boolean if it is a valid boolean (`true` or `false` or `0` for `false` ).
- `int` or `float` to `bool`: the value will be converted to a boolean. `0` will be `false`, and any other value will be `true`.
- `bool` to `int`: `true` will be `1` and `false` will be `0`.
- `bool` to `float`: `true` will be `1.0` and `false` will be `0.0`.
- `bool` to `string`: `true` will be `"true"` and `false` will be `"false"`.

> The default value for `ddn\typedobject\STRICT_TYPE_CHECKING` is `true`, so the types will be strictly checked.

If wanted and extended type conversion, the constant `ddn\typedobject\EXTENDED_TYPE_CONVERSION` can be set to `true`. If so, the following type conversions will be done:

- If the target type is `list[type]` and the value can be converted to the `type`, it will be converted to a list with a single element. 
    - e.g. `42` will be converted to `[42]` if the target type is `list[int]`.
    - e.g. `42` will be converted to `["42"]` if the target type is `list[string]`.
- If the target type is `dict[type]` and the value can be converted to the `type`, it will be converted to a dictionary with a single element. The key will be the name of the attribute.
    - e.g. `42` will be converted to `["0" => 42]` if the target type is `dict[int]`.
    - e.g. `42` will be converted to `["0" => "42"]` if the target type is `dict[string]`.

> The default value for `ddn\typedobject\EXTENDED_TYPE_CONVERSION` is `false`, so the types will be strictly checked.

**Note:** The extended type conversion has only sense if `ddn\typedobject\STRICT_TYPE_CHECKING` is set to `false`.

### Enumerations

The library also supports enumerations. To define an attribute as an enumeration, the type must be defined as `enum[<EnumValues derived class>]`, where _EnumValues_ is a subclass of the `EnumValues` class.

To define an enumeration, the class must extend the `EnumValues` class and define the possible values by means of the `VALUES` constant, which must be a list of the possible values for the enumeration, where the first value is the default value.

e.g.

```php
class UserType extends EnumValues {
    const VALUES = [
        'admin', 'user', 'guest'
    ];
}
```

Then, the attribute can be defined as an enumeration:

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        'name' => 'string',
        'type' => 'enum[UserType]',
    ];
}
```

And then we can use the enumeration like this:

```php
$user = new User();
$user->name = "John Doe";
$user->type = "admin";
```

In this example, the attribute _type_ is defined as an enumeration, so it can only be set to one of the possible values defined in the `UserType` class. If we try to set it to a value that is not in the list, an exception will be raised.

```php
$user->type = "invalid"; // This will raise an exception
```

#### Advanced usage of enumerations

The possible values in the `VALUES` constant do not need to be strings. They can be any type, even mixed types. In any case, the first value in the list will be considered to be the default value for the enumeration.

e.g.

```php
class UserType extends EnumValues {
    const VALUES = [
        0, "user", 1.0, true
    ];
}
```

In this example, the possible values are `0`, `user`, `1.0` and `true`. If we try to set the attribute _type_ to any of these values, it will be accepted. 

```php
$user = new User();
$user->name = "John Doe";
$user->type = 0; // This will work
$user->type = "user"; // This will work
$user->type = 1.0; // This will work
$user->type = true; // This will work
```

The key point for the enumerations is that the class must implement the `EnumValues` interface:
- `is_valid($value)`, will be used to check if the value is valid for the enumeration. The method must return `true` if the value is valid, and `false` otherwise.
- `get_values()`, will be used to get the list of possible values for the enumeration. The method must return an array with the possible values.

The default implementation of the `EnumValues` class is based on the existence of the `VALUES` constant. But it is possible to implement the interface in any way that is needed.

### Inheritance

`TypedObject`s are also able to inherit attributes from their parent classes. Take the following example:

```php
class Vehicle extends TypedObject {
    const ATTRIBUTES = [
        "brand" => "string",
        "color" => "string"
    ]
}
class Car extends Vehicle {
    const ATTRIBUTES = [
        "wheels" => "int"
    ]
}
class Boat extends Vehicle {
    const ATTRIBUTES = [
        "length" => "float"
    ]
}
```

In this example, class `Vehicle` will only have attribute _brand_ and _color_, but class `Car` will have _brand_, _color_ and _wheels_ attributes, while class `Boat` will have _brand_, _color_ and _length_ attributes.

### Creation of objects

Objects from children classes of `TypedObject` can be created using the static method `::fromArray` or `::fromObject`, starting from a _json_ parsed object.

In the previous example, if we have a file _car.json_ with the following content: 

```json
{
    "brand": "BMW",
    "color": "black"
}
```

We can use the following code to get an instance of the `Vehicle` class:

```php
$json = file_get_contents("car.json");
$vehicle = Vehicle::fromArray((array)json_decode($json, true));
```

An alternative is to instantiate objects like in the next example 

__*__ _PHP 8_ and over:

```php
$car = new Car(brand: "BMW", color: "black", wheels: 4);
```

__*__ previous PHP versions: 

```php
$car = new Car([ "brand" => "BMW", "color" => "black", "wheels" => 4]);
```

### Methods for the objects

#### `TypedObject`

The `TypedObject` is the core class for this library. Its methods are:

- `__construct($data)` - Creates a new object from the given data
- `__get($name)` - Returns the value of the attribute with the given name
- `__set($name, $value)` - Sets the value for the attribute with the given name
- `__isset($name)` - Returns true if the attribute with the given name is set
- `__unset($name)` - Unsets the value of an optional attribute (or resets the value of a mandatory attribute).
- `toObject()` - Returns an object with the data of the object as attributes. The array is created recursively, visiting each of the sub-attributes for each attribute.
- `toJson()` - Returns a json string with the representation of the object as standard object.
- `::fromArray($data)` - Creates an object, by parsing the given associative array into the attributes defined in the class. Each of the attributes is recursively parsed, according to the type defined to it.
- `::fromObject($data)` - Creates an object, by parsing the given object into the attributes defined in the class. Each of the attributes is recursively parsed, according to the type defined to it.
- `::fromJson($json_string)` - Creates an object, by parsing the given json string into the attributes defined in the class. Each of the attributes is recursively parsed, according to the type defined to it.
- `is_initialized()` - Returns true if all the attributes have been initialized.
- `get_uninitialized_attributes()` - Returns an array with the names of the attributes that have not been initialized.

#### `TypedDict`

This object is used to deal with a typed dictionary. The `TypedDict` class is typed to that each of the elements must be from a given type. The `TypedDict` objects can be used as array-like objects (e.g. `$dict["key1"]`).

To use a `TypedDict` (i.e. a dictionary) for the type of an attribute of a `TypedObject`, the type must be defined as `dict[type]`, where _type_ is the type of the elements in the dictionary.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        ...
        'phones' => 'dict[string]'
    ];
}
$user = new User();
$user->phones = [ "home" => "123456789", "work" => "987654321" ];
```

This code will convert the array into a `TypedDict` object.

But the `TypedDict` object can be used directly, by creating a new object of that type. In that case, the type of the elements must be defined when creating the object.

e.g.

```php
$dict = new TypedDict("int");
$dict["key1"] = 1;
$dict["key2"] = 2;
```

The methods are:
- `toObject()`: returns an StdClass object with the content of the dict.
- `toJson()`: returns a json string with the content of the dict.
- `keys()`: returns an array with the keys of the dict.
- `values()`: returns an array with the values of the dict.
- `count()`: returns the number of elements in the dict.
- `filter(callable $callback)`: returns a new `TypedDict` with the elements that satisfy the condition given by the callback.
- `::fromArray($data)`: creates a new `TypedDict` from the given associative array, using the keys as the indexes for the dict.
- `::fromObject($data)`: creates a new `TypedDict` from the given object, using the attributes as the indexes for the dict and the values as the values for the dict.

These methods are interpreted in the same way than in the case of `TypedObject`. And the type of the elements in the dict may refer to complex types that will be considered recursively when parsing the content.

e.g. type `TypedDict("list[int]")` will be used to parse `[ [ 1, 2, 3], [ 4, 5, 6 ]]`

#### `TypedList`

This object is very much the same than `TypedDict` with the exception that the indexes must be integer numbers. In this case `$value["key1"]` will produce an exception.

The idea is to use the object as an array-like object, but with the type of the elements defined.

e.g.

```php
class User extends TypedObject {
    const ATTRIBUTES = [
        ...
        'phones' => 'list[string]'
    ];
}
$user = new User();
$user->phones = [ "123456789", "987654321" ];
$user->phones[] = "000000000";
echo($user->phones[0]);
echo($user->phones[-1]);
```

In the code above, the attribute _phones_ is defined as a list of strings. So, it is possible to append a new string to the list, and to get the first and the last elements of the list.

The methods are:

- `toObject()`: returns an StdClass object with the content of the list.
- `toJson()`: returns a json string with the content of the list.
- `count()`: returns the number of elements in the list.
- `append(...$values)`: appends the given values to the list.
- `pop()`: removes the last element of the list and returns it.
- `shift()`: removes the first element of the list and returns it.
- `unshift(...$values)`: adds the given values to the beginning of the list.
- `slice($start, $end)`: returns a new `TypedList` with the elements from the start index to the end index.
- `sort(callable $callback = null)`: sorts the list using the given callback. If no callback is given, it will sort the list using the default comparison function.
- `filter(callable $callback)`: filters the list using the given callback. The callback must return a boolean value. If the callback returns `true`, the element will be included in the resulting list. If it returns `false`, the element will be discarded.
- `::fromArray($data)`: creates a new `TypedList` from the given array.

> A list in PHP is an array with integer keys starting from 0 and increasing by 1 for each element. But one can use any integer key to set a value in an array. That means that if `$l = [ 1, 2, 3 ]; $l[8] = 4; echo(count($l));` will output `4`. That means that it has _somehow_ converted to a dictionary. This feature is not supported in `TypedList`, so that it will raise an exception if a non-sequential index is used to set a value.

## Controlling the behavior of the library

The behavior of the library can be controlled by setting some constants before including the library. The constants are:

- `ddn\typedobject\STRICT_TYPE_CHECKING`: if set to `false`, the types will not be strictly checked. The default value is `true`.
- `ddn\typedobject\USE_DEFAULT_VALUES`: if set to `true`, the uninitialized attributes that do not have a default value will be initialized with the default values. The default value is `false`.
- `ddn\typedobject\USE_UNINITIALIZED_STATE`: if set to `true`, the uninitialized attributes will be considered to be in the `null` state. The default value is `true`.
- `ddn\typedobject\UNINITIALIZED_NULLABLE_IS_NULL`: if set to `true`, the uninitialized nullable attributes will be considered to be `null`. The default value is `true`.
- `ddn\typedobject\EXTENDED_TYPE_CONVERSION`: if set to `true`, the extended type conversion will be done. The default value is `false`.
- `ddn\typedobject\EMPTY_IS_ZERO`: if set to `true`, the empty string will be considered as `0` when converting to a number. The default value is `false`.

Take into account that the constants must be set before including the library, and they will affect the behavior of the library for the whole script.

e.g.

```php
define('ddn\\typedobject\\STRICT_TYPE_CHECKING', false);
include('vendor/autoload.php');
```

> The constants are defined in the namespace `ddn\typedobject`, so they must be accessed as `ddn\typedobject\STRICT_TYPE_CHECKING`.