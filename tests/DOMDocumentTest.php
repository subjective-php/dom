<?php
namespace ChadicusTest\DOM;

use Chadicus\Util\DOMDocument;

/**
 * Unit tests for the \Chadicus\Util\DOMDocument class.
 *
 * @coversDefaultClass \Chadicus\Util\DOMDocument
 * @covers ::<private>
 */
final class DOMDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of fromArray().
     *
     * @test
     * @covers ::fromArray
     *
     * @return void
     */
    public function fromArraySimpleStructure()
    {
        $document = DOMDocument::fromArray(include __DIR__ . '/_files/simple.php');
        $document->formatOutput = true;
        $this->assertSame(
            file_get_contents(__DIR__ . '/_files/simple.xml'),
            $document->saveXml()
        );
    }

    /**
     * Verify behavior of fromArray() with a more complex structure.
     *
     * @test
     * @covers ::fromArray
     *
     * @return void
     */
    public function fromArrayComplexStructure()
    {
        $document = DOMDocument::fromArray(include __DIR__ . '/_files/complex.php');
        $document->formatOutput = true;
        $this->assertSame(
            file_get_contents(__DIR__ . '/_files/complex.xml'),
            $document->saveXml()
        );
    }

    /**
     * Verify behavior of fromArray() when a key is an invlid tag name.
     *
     * @test
     * @covers ::fromArray
     * @expectedException \DOMException
     * @expectedExceptionMessage XPath [1]/foo is not valid.
     *
     * @return void
     */
    public function fromArrayInvalidKey()
    {
        DOMDocument::fromArray([['foo' => 'bar']]);
    }

    /**
     * Verify behavior of fromArray() with empty array.
     *
     * @test
     * @covers ::fromArray
     *
     * @return void
     */
    public function fromArrayEmpty()
    {
        $document = DOMDocument::fromArray([]);
        $document->formatOutput = true;
        $this->assertSame(
            "<?xml version=\"1.0\"?>\n",
            $document->saveXml()
        );
    }

    /**
     * Verify behavior of fromArray() with single element with attribute.
     *
     * @test
     * @covers ::fromArray
     *
     * @return void
     */
    public function fromArraySingleElementWithAttribute()
    {
        $document = DOMDocument::fromArray(['foo' => ['@id' => 'bar']]);
        $document->formatOutput = true;
        $this->assertSame(
            "<?xml version=\"1.0\"?>\n<foo id=\"bar\"/>\n",
            $document->saveXml()
        );
    }

    /**
     * Verify basic behavior of toArray().
     *
     * @test
     * @covers ::toArray
     *
     * @return void
     */
    public function toArraySimpleStructure()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/simple.xml');
        $array = DOMDocument::toArray($document);
        $expected = include __DIR__ . '/_files/simple.php';
        $this->assertSame($expected, $array);
    }

    /**
     * Verify behavior of toArray() with a more complex structure.
     *
     * @test
     * @covers ::toArray
     *
     * @return void
     */
    public function toArrayComplexStructure()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/complex.xml');
        $array = DOMDocument::toArray($document);
        $expected = include __DIR__ . '/_files/complex.php';
        $this->assertSame($expected, $array);
    }
}