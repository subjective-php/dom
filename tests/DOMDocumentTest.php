<?php
namespace Chadicus\Util;

/**
 * Unit tests for the \Chadicus\Util\DOMDocument class.
 *
 * @coversDefaultClass \Chadicus\Util\DOMDocument
 * @covers ::<private>
 */
final class DOMDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of isValidTagName().
     *
     * @test
     * @covers ::isValidTagName()
     *
     * @return void
     */
    public function isValidTagName()
    {
        $this->assertTrue(DOMDocument::isValidTagName('tagName'));
        $this->assertFalse(DOMDocument::isValidTagName('@tagName'));
    }

    /**
     * Verify basic behaviour of fromXPath().
     *
     * @test
     * @covers ::fromXPath
     * @uses \Chadicus\Util\DOMDocument::isValidTagName
     * @uses \Chadicus\Util\DOMDocument::createElementFromFragment
     *
     * @return void
     */
    public function fromXPath()
    {
        $document = DOMDocument::fromXPath('/root/parent/child/foo/bar');
        $expected = <<<EOT
<?xml version="1.0"?>
<root><parent><child><foo><bar/></foo></child></parent></root>

EOT;
        $this->assertSame($expected, $document->saveXml());
    }
}
