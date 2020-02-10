<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Blob;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
    }

    /**
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", true]
     *              ["1307DHDCQBHHWNCHKBD0",  false]
     *              ["1307DHDCQBHHWNCHKBDT0",  false]
     *              ["1307DHDCQBHHWNCHKBDT0PD",  false]
     *              ["",  false]
     *              [null,  false]
     *
     * @param string $authcode
     * @param bool   $expectedResult
     */
    public function testIsTicketAttachment($authcode, $expectedResult)
    {
        // GIVEN
        $blob = new Blob();
        $blob->setAuthCode($authcode);

        // WHEN/THEN
        $this->assertEquals($expectedResult, $blob->isTicketAttachment());
    }

    /**
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", false]
     *              ["1307DHDCQBHHWNCHKBD0",  false]
     *              ["1307DHDCQBHHWNCHKBDT0",  false]
     *              ["1307DHDCQBHHWNCHKBDT0PD",  true]
     *              ["",  false]
     *              [null,  false]
     *
     * @param string $authcode
     * @param bool   $expectedResult
     */
    public function testIsDownloadAttachment($authcode, $expectedResult)
    {
        // GIVEN
        $blob = new Blob();
        $blob->setAuthCode($authcode);

        // WHEN/THEN
        $this->assertEquals($expectedResult, $blob->isDownloadAttachment());
    }

    /**
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", true, true]
     *              ["1307DHDCQBHHWNCHKBD0T", false, false]
     *              ["1307DHDCQBHHWNCHKBD0",  true, false]
     *              ["1307DHDCQBHHWNCHKBD0",  false, false]
     *              ["1307DHDCQBHHWNCHKBD0PD", true, true]
     *              ["1307DHDCQBHHWNCHKBD0PD", false, false]
     *
     * @param string $authcode
     * @param bool   $shouldHaveAccessToken
     * @param mixed $isAttachmentAuthEnabled
     */
    public function testGetDownloadUrl($authcode, $isAttachmentAuthEnabled, $shouldHaveAccessToken)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withSettings([
                'core_tickets.attachment_require_auth'   => $isAttachmentAuthEnabled,
                'user.attachment_require_auth_downloads' => $isAttachmentAuthEnabled,
            ])
            ->get();

        $mockRouter = m::mock('Symfony\\Component\\Routing\\Router');
        $mockRouter->shouldIgnoreMissing();
        $mockRouter->shouldReceive('generate')->andReturn('/file.php/somecode/index.jpg');
        App::$container->shouldReceive('get')->with('router')->andReturn($mockRouter);
        App::$container->shouldReceive('generateStaticSecurityToken')->andReturn('abcd');

        $blob = new Blob();
        $blob->setAuthCode($authcode);
        $blob->setFilename('image.jpeg');

        // WHEN
        $url         = $blob->getDownloadUrl();
        $urlAbsolute = $blob->getDownloadUrl(true);

        // THEN
        $urlQueryParams         = [];
        $urlAbsoluteQueryParams = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $urlQueryParams);
        parse_str(parse_url($urlAbsolute, PHP_URL_QUERY), $urlAbsoluteQueryParams);

        if ($shouldHaveAccessToken) {
            $this->assertArrayHasKey('access_token', $urlQueryParams);
            $this->assertArrayHasKey('access_token', $urlAbsoluteQueryParams);
            $this->assertEquals('abcd', $urlQueryParams['access_token']);
            $this->assertEquals('abcd', $urlAbsoluteQueryParams['access_token']);
        } else {
            $this->assertArrayNotHasKey('access_token', $urlQueryParams);
            $this->assertArrayNotHasKey('access_token', $urlAbsoluteQueryParams);
        }
    }

    /**
     * @testWith [true,  "1307DHDCQBHHWNCHKBD0T",  "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1&access_token=abc"]
     *           [true,  "1307DHDCQBHHWNCHKBD0T",  "/file.php/somecode/index.jpg",                 "/file.php/somecode/index.jpg?access_token=abc"]
     *           [false, "1307DHDCQBHHWNCHKBD0T",  "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1"]
     *           [true,  "1307DHDCQBHHWNCHKBD0PD", "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1&access_token=abc"]
     *           [false, "1307DHDCQBHHWNCHKBD0PD", "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1"]
     *           [true,  "1307DHDCQBHHWNCHKBD0",   "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1"]
     *           [false, "1307DHDCQBHHWNCHKBD0",   "/file.php/somecode/index.jpg?s=50&size-fit=1", "/file.php/somecode/index.jpg?s=50&size-fit=1"]
     *
     * @param string $authcode
     * @param bool   $shouldHaveAccessToken
     * @param mixed $isAttachmentAuthEnabled
     * @param mixed $routerGeneratedUrl
     * @param mixed $expectedUrl
     */
    public function testGetThumbnailUrl($isAttachmentAuthEnabled, $authcode, $routerGeneratedUrl, $expectedUrl)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withSettings([
                'core_tickets.attachment_require_auth'   => $isAttachmentAuthEnabled,
                'user.attachment_require_auth_downloads' => $isAttachmentAuthEnabled,
            ])
            ->get();

        $mockRouter = m::mock('Symfony\\Component\\Routing\\Router');
        $mockRouter->shouldIgnoreMissing();
        $mockRouter->shouldReceive('generate')->andReturn($routerGeneratedUrl);
        App::$container->shouldReceive('get')->with('router')->andReturn($mockRouter);
        App::$container->shouldReceive('generateStaticSecurityToken')->andReturn('abc');

        $blob = new Blob();
        $blob->setContentType('image/jpg');
        $blob->setAuthCode($authcode);
        $blob->setFilename('image.jpeg');

        // WHEN
        $url         = $blob->getThumbnailUrl(50, UrlGeneratorInterface::RELATIVE_PATH);
        $urlAbsolute = $blob->getThumbnailUrl(50, UrlGeneratorInterface::ABSOLUTE_PATH);

        // THEN
        $this->assertEquals($expectedUrl, $url);
        $this->assertEquals($expectedUrl, $urlAbsolute);
    }

    /**
     * @testWith ["myfile_平仮名.txt", "myfile_平仮名.txt"]
     *           ["𐐜 𐐔𐐇𐐝𐐀𐐡𐐇𐐓 𐐙𐐊𐐡𐐝𐐓/𐐝𐐇𐐗𐐊𐐤𐐔 𐐒𐐋𐐗 𐐒𐐌 𐐜 𐐡𐐀𐐖𐐇𐐤𐐓𐐝 𐐱𐑂 𐑄 𐐔𐐇𐐝𐐀𐐡𐐇𐐓 𐐏𐐆𐐅𐐤𐐆𐐚𐐊𐐡𐐝𐐆𐐓𐐆", "file.bin"]
     *           ["𐐜 𐐔𐐇𐐝𐐀𐐡𐐇𐐓 𐐙𐐊𐐡𐐝𐐓/𐐝𐐇𐐗𐐊𐐤𐐔 𐐒𐐋𐐗 𐐒𐐌 𐐜 𐐡𐐀𐐖𐐇𐐤𐐓𐐝 𐐱𐑂 𐑄 𐐔𐐇𐐝𐐀𐐡𐐇𐐓 𐐏𐐆𐐅𐐤𐐆𐐚𐐊𐐡𐐝𐐆𐐓𐐆.png", "file.png"]
     *           ["和製漢語.doc", "和製漢語.doc"]
     *           ["foo.PNG", "foo.PNG"]
     *           ["FOOBAR.DOC", "FOOBAR.DOC"]
     *
     * @param string $originalFilename
     * @param string $convertedFilename
     */
    public function testFilename($originalFilename, $convertedFilename)
    {
        $blob = new Blob();
        $this->assertEquals($convertedFilename, $blob->setFilename($originalFilename)->getFilename());
    }
}
