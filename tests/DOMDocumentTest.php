<?php
namespace SubjectivePHPTest\DOM;

use DOMException;
use SubjectivePHP\Util\DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SubjectivePHP\Util\DOMDocument
 * @covers ::<private>
 */
final class DOMDocumentTest extends TestCase
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
     * @test
     * @covers ::fromArray
     */
    public function fromArrayUseCdata()
    {
        $document = DOMDocument::fromArray(include __DIR__ . '/_files/simple.php', ['use_cdata' => true]);
        $document->formatOutput = true;
        $this->assertSame(
            file_get_contents(__DIR__ . '/_files/simple_cdata.xml'),
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
     * Verify behavior of addXPath() when $xpath is not a valid xpath expression.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathInvalidExpression()
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('XPath [1]/foo is not valid');
        DOMDocument::addXPath(new \DOMDocument(), '[1]/foo');
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

    /**
     * Verify basic behavior of addXPath() with attribute
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathWithAttribute()
    {
        $xpath = '/path/to/node/with/@attribute';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value');
        $expected = <<<XML
<?xml version="1.0"?>
<path><to><node><with attribute="value"/></node></to></path>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * @test
     * @covers ::addXPath
     */
    public function addXPathIgnoresCDataWitAttribute()
    {
        $xpath = '/path/to/node/with/@attribute';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value', true);
        $expected = <<<XML
<?xml version="1.0"?>
<path><to><node><with attribute="value"/></node></to></path>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify basic behavior of addXPath().
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathExistingElement()
    {
        $xpath = '/path/to/node';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value');
        $expected = <<<XML
<?xml version="1.0"?>
<path><to><node>value</node></to></path>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * @test
     * @covers ::addXPath
     */
    public function addXPathWithCData()
    {
        $xpath = '/path/to/node';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value', true);
        $expected = <<<XML
<?xml version="1.0"?>
<path><to><node><![CDATA[value]]></node></to></path>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify behavior of addXPath() when element exists.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPath()
    {
        $xpath = '/path/to/node';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value');
        $this->assertSame("<?xml version=\"1.0\"?>\n<path><to><node>value</node></to></path>\n", $document->saveXml());
        DOMDocument::addXPath($document, $xpath, 'new value');
        $this->assertSame(
            "<?xml version=\"1.0\"?>\n<path><to><node>new value</node></to></path>\n",
            $document->saveXml()
        );
    }

    /**
     * Verify behavior of addXPath() when xpath contains child element with value.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathChildElementWithValue()
    {
        $xpath = '/root/parent[child1 = "child 1 value"]/child2';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'child 2 value');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent>
    <child1>child 1 value</child1>
    <child2>child 2 value</child2>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify behavior of addXPath() when xpath specifies the numeric index.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathWithNumericIndex()
    {
        $xpath = '/root/parent/child[3]';
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent>
    <child/>
    <child/>
    <child>value</child>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify behavior of addXPath() when xpath specifies the numeric index that exists.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathWithExistingNumericIndex()
    {
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, '/root/parent/child[3]', 'value 3');
        DOMDocument::addXPath($document, '/root/parent/child[2]', 'value 2');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent>
    <child/>
    <child>value 2</child>
    <child>value 3</child>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify behavior of addXPath() when xpath specifies attribute with value.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathWithAttributeValue()
    {
        $xpath = "/root/parent[@attr='foo']/child";
        $document = new \DOMDocument();
        DOMDocument::addXPath($document, $xpath, 'value');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent attr="foo">
    <child>value</child>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify behavior of addXPath() when xpath specifies attribute with value and attribute exists.
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathWithAttributeValueExists()
    {
        $document = new \DOMDocument();
        $document->loadXml('<root><parent attr="foo" /></root>');

        $xpath = "/root/parent[@attr='foo']/child";
        DOMDocument::addXPath($document, $xpath, 'value');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent attr="foo">
    <child>value</child>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }

    /**
     * Verify value is encoding when calling addXPath().
     *
     * @test
     * @covers ::addXPath
     *
     * @return void
     */
    public function addXPathEncodesValue()
    {
        $document = new \DOMDocument();
        $document->loadXml('<root><parent /></root>');

        $xpath = "/root/parent/child";
        DOMDocument::addXPath($document, $xpath, 'this & that');
        $document->formatOutput = true;
        $expected = <<<XML
<?xml version="1.0"?>
<root>
  <parent>
    <child>this &amp; that</child>
  </parent>
</root>

XML;
        $this->assertSame($expected, $document->saveXml());
    }
}
