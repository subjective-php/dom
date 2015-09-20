# Chadicus\DOM
[![Build Status](http://img.shields.io/travis/chadicus/dom-php.svg?style=flat)](https://travis-ci.org/chadicus/dom-php)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/chadicus/dom-php.svg?style=flat)](https://scrutinizer-ci.com/g/chadicus/dom-php/)
[![Code Coverage](http://img.shields.io/coveralls/chadicus/dom-php.svg?style=flat)](https://coveralls.io/r/chadicus/dom-php)

[![Latest Stable Version](http://img.shields.io/packagist/v/chadicus/dom.svg?style=flat)](https://packagist.org/packages/chadicus/dom)
[![Total Downloads](http://img.shields.io/packagist/dt/chadicus/dom.svg?style=flat)](https://packagist.org/packages/chadicus/dom)
[![License](http://img.shields.io/packagist/l/chadicus/dom.svg?style=flat)](https://packagist.org/packages/chadicus/dom)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://www.pholiophp.org/chadicus/dom)

A collection of utility classes to work with PHP DOM Objects

## Requirements

chadicus\dom requires PHP 5.4 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`chadicus/dom` to your project's `composer.json` file such as:

```json
{
    "require": {
        "chadicus/dom": "dev-master"
    }
}
```
##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/chadicus/dom-php/pulls)
 * [Issues](https://github.com/chadicus/dom-php/issues)

##Run Unit Tests
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
composer install
./vendor/bin/phpunit
```
## Examples

* Convert an xml document to an array
```php
<?php
use Chadicus\Util;

$xml = <<<XML
<?xml version="1.0"?>
<catalog>
  <book id="bk101">
    <author>Gambardella, Matthew</author>
    <title>XML Developer's Guide</title>
    <genre>Computer</genre>
    <price>44.95</price>
    <publish_date>2000-10-01</publish_date>
    <description>An in-depth look at creating applications with XML.</description>
  </book>
  <book id="bk102">
    <author>Ralls, Kim</author>
    <title>Midnight Rain</title>
    <genre>Fantasy</genre>
    <price>5.95</price>
    <publish_date>2000-12-16</publish_date>
    <description>A former architect battles corporate zombies, an evil sorceress, and her own childhood to become queen of the world.</description>
  </book>
XML;

$document = new \DOMDocument();
$document->loadXml($xml);
$array = Util\DOMDocument::toArray($document);

var_export($array);

```

Output will be similar to:

```
array (
  'catalog' =>
  array (
    'book' =>
    array (
      0 =>
      array (
        '@id' => 'bk101',
        'author' => 'Gambardella, Matthew',
        'title' => 'XML Developer\'s Guide',
        'genre' => 'Computer',
        'price' => '44.95',
        'publish_date' => '2000-10-01',
        'description' => 'An in-depth look at creating applications with XML.',
      ),
      1 =>
      array (
        '@id' => 'bk102',
        'author' => 'Ralls, Kim',
        'title' => 'Midnight Rain',
        'genre' => 'Fantasy',
        'price' => '5.95',
        'publish_date' => '2000-12-16',
        'description' => 'A former architect battles corporate zombies, an evil sorceress, and her own childhood to become queen of the world.',
      ),
    ),
  ),
)
```


