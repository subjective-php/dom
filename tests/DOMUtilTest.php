<?php
namespace ChadicusTest;

use Chadicus\DOMUtil;

/**
 * Unit tests for the \Chadicus\DOMUtil class.
 *
 * @coversDefaultClass \Chadicus\DOMUtil
 * @covers ::<private>
 */
final class DOMUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of fromArray().
     *
     * @test
     * @covers ::fromArray
     *
     * @return void
     */
    public function fromArray()
    {
        $books = include __DIR__ . '/_files/books.php';
        $document = DOMUtil::fromArray($books);
        $document->formatOutput = true;
        $this->assertSame(
            file_get_contents(__DIR__ . '/_files/books.xml'),
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
        DOMUtil::fromArray([['foo' => 'bar']]);
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
        $document = DOMUtil::fromArray([]);
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
        $document = DOMUtil::fromArray(['foo' => ['@id' => 'bar']]);
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
    public function toArray()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/books.xml');
        $array = DOMUtil::toArray($document);
        $expected = include __DIR__ . '/_files/books.php';
        $this->assertSame($expected, $array);
    }
}
