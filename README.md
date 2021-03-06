PHP Properties
==========

<p align="center">
    <a href="https://travis-ci.org/SerafimArts/Properties"><img src="https://travis-ci.org/SerafimArts/Properties.svg?branch=master" alt="Travis CI" /></a>
    <a href="https://scrutinizer-ci.com/g/SerafimArts/Properties/?branch=master"><img src="https://scrutinizer-ci.com/g/SerafimArts/Properties/badges/quality-score.png?b=master" alt="Scrutinizer CI" /></a>
    <a href="https://packagist.org/packages/serafim/properties"><img src="https://poser.pugx.org/serafim/properties/version" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/serafim/properties"><img src="https://poser.pugx.org/serafim/properties/v/unstable" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/serafim/properties/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License MIT"></a>
    <a href="https://packagist.org/packages/serafim/properties"><img src="https://poser.pugx.org/serafim/properties/downloads" alt="Total Downloads"></a>
</p>

PHP Properties implementations based on getters or setters method and used 
[PSR-5 PHPDoc](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md) information.

- [Installation](#installation)
- [Introduction](#introduction)
- [Properties Usage](#properties-usage)
    - [Readonly Properties](#readonly-properties)
    - [Writeonly Properties](#writeonly-properties)
    - [Getters And Setters](#getters-and-setters)
    - [Autocomplete](#autocomplete)
- [Type Hints](#type-hints)
    - [Primitive Type Hints](#primitive-type-hints)
    - [Arrays And Generics](#arrays-and-generics)
    - [Conjunction And Disjunction](#conjunction-and-disjunction)
- [Production Mode](#production-mode)


## Installation

`composer require serafim/properties`

[Package on packagist.org](https://packagist.org/packages/serafim/properties)

## Introduction

Properties provide the ability to control the behavior of setting and 
retrieving data from an object fields.

There are **no properties** at the **language level**, but they can be implemented with 
the some additional functionality. 

For example:

```php
class MyClass
{
    protected $a = 23;

    public function __get($field)
    {
        return $this->$field;
    }
}

$dto = new MyClass();
echo $dto->a; // 23
$dto->a = 42; // Cannot access protected property MyClass::$a
```

This code example does not provide type-hint and autocomplete. 
Using magic without the ability to control it is always a bad option. 
...well, it looks awful %)

We can fix some problems using PSR-5. In this case, we can add a docblock.

```php
/**
 * @property-read int $a
 */
class MyClass
{
    // ...
```

But this docblock only adds information for the IDE and does not 
affect the code itself. At the same time, you should always keep it up to date.

But wait! We have another problem.

```php
$dto = new MyClass();
echo isset($dto->a); // false
```

This is obviously a bug. We still need to add `__isset`, `__unset` and `__set` support.

It's damn awful!!111

## Properties Usage

Now let's see what the `serafim/properties` package provides.

```php
/**
 * @property $a
 */
class MyClass
{
    use Serafim\Properties\Properties;
    
    protected $a = 23;
}

$dto = new MyClass();
$dto->a;      // 23
$dto->a = 42; // Ok
$dto->a;      // 42
```

### Readonly Properties

To indicate that the type should be readonly use `@property-read` annotation.

```php
/**
 * @property-read $a
 */
class MyClass
{
    use Serafim\Properties\Properties;
    
    protected $a = 23;
}

$dto = new MyClass();
$dto->a;      // 23
$dto->a = 42; // Error: Property MyClass::$a is readonly
```

### Writeonly Properties

To indicate that the type should be readonly use `@property-write` annotation.

```php
/**
 * @property-write $a
 */
class MyClass
{
    use Serafim\Properties\Properties;
    
    protected $a = 23;
}

$dto = new MyClass();
$dto->a = 42; // 42
$dto->a;      // Error: Property MyClass::$a is writeonly
```

### Getters And Setters

For getter or setter override just declare `get[Property]` 
(`is[Property]` for `bool` values will be works too) or `set[Property]` methods.

```php
/**
 * @property-read int $a
 */
class MyClass
{
    use Serafim\Properties\Properties;
    
    protected $a = 23;

    protected function getA() 
    {
        return $this->a + 19;
    }
}

$dto = new MyClass();
echo $dto->a; // 42 (because 23 + 19 = 42)
$dto->a = 42; // Error: Property is read-only (@property-read doc declaration)
```

Setter:

```php
/**
 * @property-write string $anotherProperty
 */
class Some 
{
   // ... 
   protected $anotherProperty = 'some';
   
    /**
     * @param string $newVal
     */
   public function setAnotherProperty($newVal) 
   {
       // Just example
       if (mb_strlen($newVal) > 4) {
           throw new InvalidArgumentException('...');
       }
       
       $this->anotherProperty = $newVal;
   }
}
```

### Autocomplete

All these annotations fully work in the IDE, including 
autocomplete and highlighting incorrect behavior.

<p align="center">
    <img src="https://habrastorage.org/webt/_p/uk/uv/_pukuvt6exo-xvwyrzp2bi8u0re.png" alt="IDE Autocomplete And Highlighting" />
</p>


## Type Hints

All properties with writeable behavior will be "type checkable".

```php
/**
 * @property int|null $a
 */
class Some
{
    use Serafim\Properties\Properties;
    
    protected $a; 
}

//

$some = new Some;
$some->a = 23; // Ok
$some->a = null; // Ok
$some->a = 'string'; // Error: "TypeError: Value for property Some::$a must be of the type int|null, string given"
```

### Primitive Type Hints

- `int` (or `integer`) - property value are integer
- `bool` (or `boolean`) - value are boolean
- `float` (or `double`) - value are float
- `string` - value are string or object with `__toString` method
- `null` (or `void`) - value are nullable
- `resource` - value are resource
- `object` - value can be any object 
- `mixed` - no type checking
- `callable` - value can be string, instance of \Closure, array with 2 args or object with `__invoke` method
- `scalar` - value cannot be an object
- `countable` - value can be a countable (array or object provided `Countable` interface).
- `self` - value can be object of self class or string with name of self class
> _`self` keyword does **not available** yet: it will be supports in future_

- `static` - value can be instance of self class or string whos are sublass of self 
> _`static` keyword does **not available** yet: it will be supports in future_

- `$this` - value can be only object instance of self class
> _`$this` keyword does **not available** yet: it will be supports in future_

### Arrays And Generics

- `array` - value is type of array
- `Class[]` - value is type of array or instance of \Traversable
- `scalar[]` - value is type of array or instance of \Traversable
- `Collection<>` - value is type of array or instance of "Collection" and \Traversable
- `Collection<T>` - value is type of array or instance of "Collection" and \Traversable
- `Collection<T,V>`- value is type of array or instance of "Collection" and \Traversable

### Conjunction And Disjunction

- `a|b` - means that the value must be type `(a or b)`.
- `a&b` - means that the value must be type `(a and b)`.
- `a|b&c` - means that the value must be type `(a or (b and c))`.

See more: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#appendix-a-types

## Production Mode

The code is quite effective, but in the production mode you 
should use caching. The package implements support for the PSR-16 standard.

```php
$driver = new Psr16CacheDriver(); // Your PSR16 cache driver implementation 

$properties = Serafim\Properties\Bootstrap::getInstance();
$properties->setCacheDriver($driver);
```
