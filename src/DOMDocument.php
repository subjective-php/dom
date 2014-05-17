<?php
namespace Chadicus\Util;

/**
 * Utiltity class for \DOMDocument.
 */
class DOMDocument
{
    /**
     * Regex for a valid xml tag name.
     *
     * @var string
     */
    private static $elementNameRegex = '[a-z][\w0-9-]*';

    /**
     * Returns true if the given $name is a valid xml tag name.
     *
     * @param string $name The tag name to check.
     *
     * @return boolean
     */
    public static function isValidTagName($name)
    {
        try {
            new \DOMElement($name);
        } catch (\DOMException $e) {
            return false;
        }

        return true;
    }

    /**
     * Creates a new \DOMDocument from the given xpath.
     *
     * @param string $xpath The path from which to create the element.
     *
     * @return \DOMDocument
     */
    public static function fromXPath($xpath)
    {
        $document = new \DOMDocument();
        $elements = array();
        foreach (explode('/', $xpath) as $tagName) {
            if (trim($tagName) == '') {
                continue;
            }

            $elements[] = self::createElementFromFragment($document, $tagName);
        }

        $current = $document;
        do {
            $previous = $current;
            $current = array_shift($elements);
            $previous->appendChild($current);
        } while (count($elements));

        return $document;
    }

    /**
     * Creates an \DOMElement and all nesecary parent elements based on the given xpath.
     *
     * @param \DOMDocument $document The owner document of the new element(s).
     * @param string       $xpath    The path to the new element.
     * @param mixed        $value    The value of the new element.
     *
     * @return void
     */
    public static function addXPath(\DOMDocument $document, $xpath, $value = null)
    {
        $domXPath = new \DOMXPath($document);
        $nodeList = $domXPath->query($xpath);
        if ($nodeList->length !== 0) {
            $nodeList->item(0)->nodeValue = $value;
            return;
        }

        $xpaths = explode('/', ltrim($xpath, '/'));
        $tagName = array_pop($xpaths);
        $previous = null;
        $current = self::createElementFromFragment($document, $tagName, $value);

        while (count($xpaths)) {
            $nodeList = $domXPath->query('/' . implode('/', $xpaths));
            if ($nodeList->length !== 0) {
                $nodeList->item(0)->appendChild($current);
                return;
            }

            $tagName = array_pop($xpaths);
            $previous = $current;
            $current = self::createElementFromFragment($document, $tagName);
            $current->appendChild($previous);
        }

        $document->appendChild($current);
    }

    /**
     * Converts the given \DOMDocument to an array.
     *
     * @param \DOMDocument $document The document to convert.
     *
     * @return array
     */
    public static function toArray(\DOMDocument $document)
    {
        $result = array();
        $domXPath = new \DOMXPath($document);
        foreach ($domXPath->query('//* | //@*') as $node) {
            $xpath = trim($node->getNodePath(), '/');
            $xpath = str_replace('[', '/', $xpath);
            $xpath = str_replace(']', '', $xpath);
            $value = null;
            if ($node->childNodes->length === 1 && $node->childNodes->item(0) instanceof \DOMText) {
                $value = $node->childNodes->item(0)->nodeValue;
            }

            $result = self::pathToArray($result, $xpath, $value);
        }

        return $result;
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
        $path = ltrim($path, '/');
        $parts = explode('/', $path);

        $arrayCopy = &$array;
        foreach ($parts as $i => $part) {
            if (!isset($arrayCopy[$part])) {
                $arrayCopy[$part] = array();
            }

            //if last
            if ($i === count($parts) - 1) {
                $arrayCopy[$part] = $value;
            } else {
                $arrayCopy = &$arrayCopy[$part];
            }
        }

        return $array;
    }

    /**
     * Helper method to create a \DOMElement based on the given xpath $fragment.
     *
     * @param \DOMDocument $document The parent document of the \DOMElement.
     * @param string       $fragment XPath fragment that defines the element.
     * @param mixed        $value    The value of the new element.
     *
     * @return \DOMElement
     */
    private static function createElementFromFragment(\DOMDocument $document, $fragment, $value = null)
    {
        if (self::isValidTagName($fragment)) {
            $element = $document->createElement($fragment);
            if ($value !== null) {
                $element->nodeValue = $value;
            }

            return $element;
        }

        $pattern = '(?P<parent>' . self::$elementNameRegex
                 . ')\[(?P<child>' . self::$elementNameRegex
                 . ')\s*[=|<|>]{1,2}\s*[\'|"]?(?P<childValue>[^\'|"]*)[\'|"]?\]';
        $matches = array();
        if (preg_match("/^{$pattern}$/i", $fragment, $matches)) {
            $parent = $document->createElement($matches['parent']);
            $parent->appendChild($document->createElement($matches['child'], $matches['childValue']));
            if ($value !== null) {
                $parent->nodeValue = $value;
            }

            return $parent;
        }

        $pattern = '(?P<parent>' . self::$elementNameRegex
                 . ')\[@(?P<child>' . self::$elementNameRegex
                 . ')\s*[=|<|>]{1,2}\s*[\'|"]?(?P<childValue>[^\'|"]*)[\'|"]?\]';
        $matches = array();
        if (preg_match("/^{$pattern}$/i", $fragment, $matches)) {
            $parent = $document->createElement($matches['parent']);
            $attribute = $document->createAttribute($matches['child']);
            $attribute->value = $matchs['childValue'];
            $parent->appendChild($attribute);
            if ($value !== null) {
                $parent->nodeValue = $value;
            }

            return $parent;
        }

        $pattern = '(?P<parent>' . self::$elementNameRegex . ')\[@(?P<attribute>' . self::$elementNameRegex .')\]';
        if (preg_match("/^{$pattern}$/i", $fragment, $matches)) {
            $parent = $document->createElement($matches['parent']);
            $attribute = $document->createAttribute($matches['attribute']);
            $parent->appendChild($attribute);
            if ($value !== null) {
                $parent->nodeValue = $value;
            }

            return $parent;
        }

        if ($fragment[0] === '@') {
            $attribute = $document->createAttribute(substr($fragment, 1));
            if ($value !== null) {
                $attribute->value = $value;
            }

            return $attribute;
        }
    }

    /**
     * Creates a \DOMDocument with the given array.
     *
     * @param array $array The array to convert.
     *
     * @return \DOMDocument
     */
    public static function fromArray(array $array)
    {
        $document = new \DOMDocument();
        self::_fromArray($array, $document);
        return $document;
    }

    /**
     * Helper method to construct a document with an array.
     *
     * @param scalar|array $data    The data with which to fill the \DOMNode.
     * @param \DOMNode     $context The \DOMNode to fill with $data.
     *
     * @return void
     *
     * @throws \Exception Thrown if $data is not an array or a scalar value.
     */
    private static function fillNode($data, \DOMNode $context)
    {
        $document = ($context instanceof \DOMDocument) ? $context : $context->ownerDocument;

        if (is_scalar($data)) {
            $context->appendChild($document->createTextNode($data));
            return;
        }

        if (!is_array($data)) {
            throw new \Exception('$data was not scalar or array');
        }

        foreach ($data as $key => $value) {
            if ($key[0] === '@') {
                $context->setAttribute(substr($key, 1), $value);
                continue;
            }

            $domNode = null;
            if (!is_int($key)) {
                $domNode = $document->createElement($key);
                $context->appendChild($domNode);
            } elseif ($key === 0) {
                $domNode = $context;
            } else {
                $domNode = $document->createElement($context->tagName);
                $context->parentNode->appendChild($domNode);
            }

            //RECURSION!!!
            self::fillNode($value, $domNode);
        }
    }
}
