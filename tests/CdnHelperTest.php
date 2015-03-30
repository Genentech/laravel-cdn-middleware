<?php

use Genentech\CdnViews\Conversion\CdnHelper;
use Mockery as m;

const CDN_URL = "http://cdn.example.com";
const SSL_Enabled = true;
const SSL_Disabled = false;

class CdnHelperTest extends PHPUnit_Framework_TestCase
{
    public $validTags = ['script','img','link'];
    public $cdn_helper;

    public function setUp()
    {
        parent::setUp();
    }

    public function test_convertsBasicUrls() {
        $cdn_helper =  new CdnHelper(CDN_URL, $this->validTags, SSL_Enabled);

        $test_url = $cdn_helper->convertURL('/assets/test/img/someimage.jpg');
        $this->assertEquals(CDN_URL.'/assets/test/img/someimage.jpg', $test_url);

        $test_url = $cdn_helper->convertURL('/assets/test/img/someimage.jpg?500x500');
        $this->assertEquals(CDN_URL.'/assets/test/img/someimage.jpg?500x500', $test_url);

        $test_url = $cdn_helper->convertURL('/assets/test/img/someimage.pdf#ch22');
        $this->assertEquals(CDN_URL.'/assets/test/img/someimage.pdf#ch22', $test_url);

        $test_url = $cdn_helper->convertURL('/assets/test/img/someimage.pdf?res=500x500&trackingcode=adsfasd');
        $this->assertEquals(CDN_URL.'/assets/test/img/someimage.pdf?res=500x500&trackingcode=adsfasd', $test_url);
    }

    public function test_logsNonRootRelativeUrlsAndDoesNotConvertThem() {
        $app = m::mock('AppMock');
        $app->shouldReceive('instance')->once()->andReturn($app);

        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        Illuminate\Support\Facades\Log::swap($log = m::mock('LogMock'));

        $log->shouldReceive('warning')->once();

        $cdn_helper =  new CdnHelper(CDN_URL, $this->validTags, SSL_Enabled);
        $test_url = $cdn_helper->convertURL('assets/test/img/someimage.jpg');
        $this->assertEquals('assets/test/img/someimage.jpg', $test_url);
    }

    public function test_itConvertsWholePages() {
        $cdn_helper =  new CdnHelper(CDN_URL, $this->validTags, SSL_Enabled);
        $input = file_get_contents('tests/inputHTML.txt');
        $output = $cdn_helper->convertPageForCDN($input);
        var_dump($output);
    }

    public function tearDown()
    {
        $this->cdn_helper = null;
        m::close();
    }
}