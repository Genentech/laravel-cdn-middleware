<?php

use Genentech\CdnViews\Conversion\TagConverter;
use Mockery as M;

class TagConverterTest extends PHPUnit_Framework_TestCase
{
    public function test_itRegistersCallbacks() {
        $tag_converter = new TagConverter();
        $tag_converter->registerTag('script', function (DOMNode $node){
            $node->nodeValue = 'I Was Tested';
            return $node;
        });

        $dom = new DOMDocument('1.0', 'utf-8');
        $test_node = $dom->createElement('script', 'Not Tested');
        $result = $tag_converter->convertNode($test_node);
        $this->assertEquals('I Was Tested', $result->nodeValue);
    }

    public function test_itNestsCallbacks() {
        $tag_converter = new TagConverter();
        $tag_converter->registerTag('script', function (DOMNode $node){
            $node->nodeValue = 'I Was Tested';
            return $node;
        });

        $tag_converter->registerTag('script', function (DOMNode $node){
            $node->nodeValue = strtoupper($node->nodeValue);
            return $node;
        });

        $dom = new DOMDocument('1.0', 'utf-8');
        $test_node = $dom->createElement('script', 'Not Tested');
        $result = $tag_converter->convertNode($test_node);
        $this->assertEquals('I WAS TESTED', $result->nodeValue);
    }

    public function test_itRemovesCallbacks() {
        $tag_converter = new TagConverter();
        $tag_converter->registerTag('script', function (DOMNode $node){
            $node->nodeValue = 'I Was Tested';
            return $node;
        });

        $dom = new DOMDocument('1.0', 'utf-8');
        $test_node = $dom->createElement('script', 'Not Tested');
        $result = $tag_converter->convertNode($test_node);
        $this->assertEquals('I Was Tested', $result->nodeValue);

        $tag_converter->unregisterTag('script');
        $this->setExpectedException('\Genentech\CdnViews\Exceptions\TagNotRegisteredException');
        $tag_converter->convertNode($test_node);
    }
}