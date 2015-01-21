<?php

namespace Chadicus;

use DOMAttr;
use DOMDocument;
use DOMException;
use DOMText;
use DOMXPath;

/**
 * Static helper class for working with DOM objects.
 */
class DOMUtil
{
    /**
     * Coverts the given array to a DOMDocument.
     *
     * @param array $array The array to covert.
     *
     * @return DOMDocument
     */
    public static function fromArray(array $array)
    {
        $flattened = self::flatten($array);
        $document = new DOMDocument();
        foreach (self::flatten($array) as $path => $value) {
            self::addXPath($document, $path, $value);
        }

        return $document;
    }

    /**
     * Converts the given DOMDocument to an array.
     *
     * @param DOMDocument $document The document to convert.
     *
     * @return array
     */
    public static function toArray(DOMDocument $document)
    {
        $result = array();
        $domXPath = new DOMXPath($document);
        foreach ($domXPath->query('//* | //@*') as $node) {
            $xpath = trim($node->getNodePath(), '/');
            $xpath = str_replace('[', '/', $xpath);
            $xpath = str_replace(']', '', $xpath);
            $value = null;
            if ($node->childNodes->length === 1 && $node->childNodes->item(0) instanceof DOMText) {
                $value = $node->childNodes->item(0)->nodeValue;
            }

            $result = self::pathToArray($result, $xpath, $value);
        }

        return $result;
    }

    /**
     * Helper method to add a new DOMNode to the given document with the given value.
     *
     * @param DOMDocument $document The document to which the node will be added.
     * @param string       $xpath    A valid xpath destination of the new node.
     * @param mixed        $value    The value for the new node.
     *
     * @return void
     *
     * @throws DOMException Thrown if the given $xpath is not valid.
     */
    private static function addXPath(DOMDocument $document, $xpath, $value = null)
    {
        $pointer = $document;
        $domXPath = new DOMXPath($document);

        if (@$domXPath->evaluate($xpath) === false) {
            throw new DOMException("XPath {$xpath} is not valid.");
        }

        $xpaths = array_filter(explode('/', $xpath));

        while (count($xpaths)) {
            $tagName = array_shift($xpaths);
            $count = 1;
            $matches = [];
            $pattern = '^(?P<name>[a-z][\w0-9-]*)\[(?P<count>\d+)\]$';
            if (preg_match("/{$pattern}/i", $tagName, $matches)) {
                $tagName = $matches['name'];
                $count = (int)$matches['count'];
            }

            $path = $tagName;
            $list = $domXPath->query($path, $pointer);
            if ($tagName[0] === '@') {
                $attribute = $document->createAttribute(substr($tagName, 1));
                $pointer->appendChild($attribute);
                $pointer = $attribute;
                continue;
            }

            while ($list->length < $count) {
                $node = $document->createElement($tagName);
                $pointer->appendChild($node);
                $list = $domXPath->query($path, $pointer);
            }

            $pointer = $list->item($count -1);
        }

        if ($pointer instanceof DOMAttr) {
            $pointer->value = $value;
            return;
        }

        $pointer->nodeValue = $value;
    }

    /**
     * Helper method to create all sub elements in the given array based on the given xpath.
     *
     * @param array  $array The array to which the new elements will be added.
     * @param string $path  The xpath defining the new elements.
     * @param mixed  $value The value for the last child element.
     *
     * @return array
     */
    private static function pathToArray(array $array, $path, $value = null)
    {
        $parts = array_filter(explode('/', $path));

        $arrayCopy = &$array;
        foreach ($parts as $i => $part) {
            if (is_numeric($part)) {
                $part = ((int)$part) - 1;
            }

            if (!isset($arrayCopy[$part])) {
                $arrayCopy[$part] = array();
            }

            //if last
            if ($i === count($parts) - 1) {
                $arrayCopy[$part] = $value;
                continue;
            }

            $arrayCopy = &$arrayCopy[$part];
        }

        return $array;
    }

    /**
     * Helper method to flatten a multi-dimensional array into a single dimensional array whose keys are xpaths.
     *
     * @param array  $array  The array to flatten.
     * @param string $prefix The prefix to recursively add to the flattened keys.
     *
     * @return array
     */
    private static function flatten(array $array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_int($key)) {
                $key += 1;
                $newKey = "{$prefix}[{$key}]";
            } else {
                $newKey = $prefix . (empty($prefix) ? '' : '/') . $key;
            }

            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $newKey));
                continue;
            }

            $result[$newKey] = $value;
        }

        return $result;
    }
}
