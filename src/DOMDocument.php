<?php

namespace SubjectivePHP\Util;

/**
 * Static helper class for working with \DOM objects.
 */
abstract class DOMDocument
{
    /**
     * Coverts the given array to a \DOMDocument.
     *
     * @param array $array The array to covert.
     *
     * @return \DOMDocument
     */
    final public static function fromArray(array $array)
    {
        $document = new \DOMDocument();
        foreach (self::flatten($array) as $path => $value) {
            self::addXPath($document, $path, $value);
        }

        return $document;
    }

    /**
     * Converts the given \DOMDocument to an array.
     *
     * @param \DOMDocument $document The document to convert.
     *
     * @return array
     */
    final public static function toArray(\DOMDocument $document)
    {
        $result = [];
        $domXPath = new \DOMXPath($document);
        foreach ($domXPath->query('//*[not(*)] | //@*') as $node) {
            self::pathToArray($result, $node->getNodePath(), $node->nodeValue);
        }

        return $result;
    }

    /**
     * Helper method to add a new \DOMNode to the given document with the given value.
     *
     * @param \DOMDocument $document The document to which the node will be added.
     * @param string       $xpath    A valid xpath destination of the new node.
     * @param mixed        $value    The value for the new node.
     *
     * @return void
     *
     * @throws \DOMException Thrown if the given $xpath is not valid.
     */
    final public static function addXPath(\DOMDocument $document, string $xpath, $value = null)
    {
        $domXPath = new \DOMXPath($document);
        $list = @$domXPath->query($xpath);
        if ($list === false) {
            throw new \DOMException("XPath {$xpath} is not valid.");
        }

        if ($list->length) {
            $list->item(0)->nodeValue = $value;
            return;
        }

        $pointer = $document;
        foreach (array_filter(explode('/', $xpath)) as $tagName) {
            $pointer = self::parseFragment($domXPath, $pointer, $tagName);
        }

        if ($value !== null) {
            $pointer->nodeValue = htmlentities($value, ENT_XML1, $document->encoding, false);
        }
    }

    /**
     * Helper method to create element(s) from the given tagName.
     *
     * @param \DOMXPath $domXPath The DOMXPath object built using the owner document.
     * @param \DOMNode  $context  The node to which the new elements will be added.
     * @param string    $fragment The tag name of the element.
     *
     * @return \DOMElement|\DOMAttr The DOMNode that was created.
     */
    final private static function parseFragment(\DOMXPath $domXPath, \DOMNode $context, string $fragment)
    {
        $document = $domXPath->document;

        if ($fragment[0] === '@') {
            $attributeName = substr($fragment, 1);
            $attribute = $context->attributes->getNamedItem($attributeName);
            if ($attribute === null) {
                $attribute = $document->createAttribute($attributeName);
                $context->appendChild($attribute);
            }

            return $attribute;
        }

        $matches = [];

        //match fragment with comparision operator (ex parent[child="foo"])
        $pattern = '^(?P<parent>[a-z][\w0-9-]*)\[(?P<child>[a-z@][\w0-9-]*)\s*=\s*["\'](?P<value>.*)[\'"]\]$';
        if (preg_match("/{$pattern}/i", $fragment, $matches)) {
            //Find or create the parent node
            $list = $domXPath->query($fragment, $context);
            $parent = $list->length ? $list->item(0) : $document->createElement($matches['parent']);
            //If child is an attribute, create and append. Attributes are overwritten if they exist
            if ($matches['child'][0] == '@') {
                $attribute = $document->createAttribute(substr($matches['child'], 1));
                $attribute->value = $matches['value'];
                $parent->appendChild($attribute);
                $context->appendChild($parent);
                return $parent;
            }

            //Assume child does not exist
            $parent->appendChild($document->createElement($matches['child'], $matches['value']));
            $context->appendChild($parent);
            return $parent;
        }

        //If the fragment did not match the above pattern, then assume it is
        //either '/parent/child  or /parent/child[n] Where n is the nth occurence of the child node
        $matches = [];
        preg_match('/^(?P<name>[a-z][\w0-9-]*)\[(?P<count>\d+)\]$/i', $fragment, $matches);
        //default count and name if pattern doesn't match.
        //There may be another pattern that I'm missing and should account for
        $matches += ['count' => 1, 'name' => $fragment];

        $count = $matches['count'];
        $tagName = $matches['name'];

        $list = $domXPath->query($tagName, $context);
        self::addMultiple($document, $context, $tagName, $count - $list->length);

        return $domXPath->query($tagName, $context)->item($count - 1);
    }

    /**
     * Helper method to add multiple identical nodes to the given context node.
     *
     * @param \DOMDocument $document The parent document.
     * @param \DOMNode     $context  The node to which the new elements will be added.
     * @param string       $tagName  The tag name of the element.
     * @param integer      $limit    The number of elements to create.
     *
     * @return void
     */
    final private static function addMultiple(\DOMDocument $document, \DOMNode $context, string $tagName, int $limit)
    {
        for ($i = 0; $i < $limit; $i++) {
            $context->appendChild($document->createElement($tagName));
        }
    }

    /**
     * Helper method to create all sub elements in the given array based on the given xpath.
     *
     * @param array  $array The array to which the new elements will be added.
     * @param string $path  The xpath defining the new elements.
     * @param mixed  $value The value for the last child element.
     *
     * @return void
     */
    final private static function pathToArray(array &$array, string $path, $value = null)
    {
        $path = str_replace(['[', ']'], ['/', ''], $path);
        $parts = array_filter(explode('/', $path));
        $key = array_shift($parts);

        if (is_numeric($key)) {
            $key = (int)$key -1;
        }

        if (empty($parts)) {
            $array[$key] = $value;
            return;
        }

        self::arrayize($array, $key);

        //RECURSION!!
        self::pathToArray($array[$key], implode('/', $parts), $value);
    }

    /**
     * Helper method to ensure the value at the given $key is an array.
     *
     * @param array  $array The array for which element $key should be checked.
     * @param string $key   The key for which the value will be made into an array.
     *
     * @return void
     */
    final private static function arrayize(array &$array, string $key)
    {
        if (!array_key_exists($key, $array)) {
            //key does not exist, set to empty array and return
            $array[$key] = [];
            return;
        }

        if (!is_array($array[$key])) {
            //key exists but is not an array
            $array[$key] = [$array[$key]];
        }//else key exists and is an array
    }

    /**
     * Helper method to flatten a multi-dimensional array into a single dimensional array whose keys are xpaths.
     *
     * @param array  $array  The array to flatten.
     * @param string $prefix The prefix to recursively add to the flattened keys.
     *
     * @return array
     */
    final private static function flatten(array $array, string $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = self::getNewKey($key, $prefix);
            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $newKey));
                continue;
            }

            $result[$newKey] = $value;
        }

        return $result;
    }

    final private static function getNewKey(&$key, string $prefix) : string
    {
        if (is_int($key)) {
            return (substr($prefix, -1) == ']') ? $prefix : "{$prefix}[" . (++$key) . ']';
        }

        return $prefix . (empty($prefix) ? '' : '/') . $key;
    }
}
