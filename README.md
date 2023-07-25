# SubjectivePHP\DOM

[![Latest Stable Version](https://poser.pugx.org/subjective-php/dom/v/stable)](https://packagist.org/packages/subjective-php/dom)
[![Latest Unstable Version](https://poser.pugx.org/subjective-php/dom/v/unstable)](https://packagist.org/packages/subjective-php/dom)
[![License](https://poser.pugx.org/subjective-php/dom/license)](https://packagist.org/packages/subjective-php/dom)

[![Total Downloads](https://poser.pugx.org/subjective-php/dom/downloads)](https://packagist.org/packages/subjective-php/dom)
[![Monthly Downloads](https://poser.pugx.org/subjective-php/dom/d/monthly)](https://packagist.org/packages/subjective-php/dom)
[![Daily Downloads](https://poser.pugx.org/subjective-php/dom/d/daily)](https://packagist.org/packages/subjective-php/dom)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://www.pholiophp.org/subjective-php/dom)

A collection of utility classes to work with PHP DOM Objects

## Requirements

subjective-php\dom requires PHP 7.0 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`subjective-php/dom` to your project's `composer.json` file such as:

```sh
composer require subjective-php/dom
```
##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/subjective-php/dom/pulls)
 * [Issues](https://github.com/subjective-php/dom/issues)

##Run Unit Tests
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
composer install
./vendor/bin/phpunit
```
# Examples

### Convert an xml document to an array
```php
<?php
use SubjectivePHP\Util;

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

### Convert an array to XML
```php
<?php
use SubjectivePHP\Util;

$catalog = [
    'book' => [
        [
            '@id' => '58339e95d52d9',
            'author' => 'Corets, Eva',
            'title' => 'The Sundered Grail',
            'genre' => 'Fantasy',
            'price' => 5.95,
            'published' => 1000094400,
            'description' => "The two daughters of Maeve, half-sisters, battle one another for control of England. Sequel to Oberon's Legacy.",
        ],
        [
            '@id' => '58339e95d530e',
            'author' => 'Randall, Cynthia',
            'title' => 'Lover Birds',
            'genre' => 'Romance',
            'price' => 4.95,
            'published' => 967867200,
            'description' => 'When Carla meets Paul at an ornithology conference, tempers fly as feathers get ruffled.',
        ],
    ],
];

$document = Util\DOMDocument::fromArray(['catalog' => $catalog]);
$document->formatOutput = true;
echo $document->saveXml();
```
#### Output

```
<?xml version="1.0"?>
<catalog>
  <book id="58339e95d52d9">
    <author>Corets, Eva</author>
    <title>The Sundered Grail</title>
    <genre>Fantasy</genre>
    <price>5.95</price>
    <published>1000094400</published>
    <description>The two daughters of Maeve, half-sisters, battle one another for control of England. Sequel to Oberon's Legacy.</description>
  </book>
  <book id="58339e95d530e">
    <author>Randall, Cynthia</author>
    <title>Lover Birds</title>
    <genre>Romance</genre>
    <price>4.95</price>
    <published>967867200</published>
    <description>When Carla meets Paul at an ornithology conference, tempers fly as feathers get ruffled.</description>
  </book>
</catalog>
```

### Construct XML document using xpaths

```php
<?php
use SubjectivePHP\Util;

$document = new DOMDocument();
$document->formatOutput = true;
Util\DOMDocument::addXPath($document, "/catalog/book[@id='58339e95d530e']/title", 'Lover Birds');
Util\DOMDocument::addXPath($document, '/catalog/book[@id="58339e95d530e"]/price', 4.95);
Util\DOMDocument::addXPath($document, '/catalog/book[@id="58339e95d52d9"]/title', 'The Sundered Grail');
Util\DOMDocument::addXPath($document, '/catalog/book[@id="58339e95d52d9"]/genre', 'Fantasy');
echo $document->saveXml();
```
#### Output
```
<?xml version="1.0"?>
<catalog>
  <book id="58339e95d530e">
    <title>Lover Birds</title>
    <price>4.95</price>
  </book>
  <book id="58339e95d52d9">
    <title>The Sundered Grail</title>
    <genre>Fantasy</genre>
  </book>
</catalog>
```
