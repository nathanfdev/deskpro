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
        //$this->container       = ContainerMock::create()->get();
        $this->containerBefore = App::$container;
        //App::$container        = $this->container;
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
    }

    /**
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", true]
     *              ["1307DHDCQBHHWNCHKBD0",  false]
     *              ["1307DHDCQBHHWNCHKBDT0",  false]
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
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", true, true]
     *              ["1307DHDCQBHHWNCHKBD0T", false, false]
     *              ["1307DHDCQBHHWNCHKBD0",  true, false]
     *              ["1307DHDCQBHHWNCHKBD0",  false, false]
     *
     * @param string $authcode
     * @param bool   $shouldHaveAccessToken
     */
    public function testGetDownloadUrl($authcode, $isAttachmentAuthEnabled, $shouldHaveAccessToken)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withSettings(['core_tickets.attachment_require_auth' => $isAttachmentAuthEnabled])
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
     * @testWith    ["1307DHDCQBHHWNCHKBD0T", true, true]
     *              ["1307DHDCQBHHWNCHKBD0T", false, false]
     *              ["1307DHDCQBHHWNCHKBD0",  true, false]
     *              ["1307DHDCQBHHWNCHKBD0",  false, false]
     *
     * @param string $authcode
     * @param bool   $shouldHaveAccessToken
     */
    public function testGetThumbnailUrl($authcode, $isAttachmentAuthEnabled, $shouldHaveAccessToken)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withSettings(['core_tickets.attachment_require_auth' => $isAttachmentAuthEnabled])
            ->get();

        $mockRouter = m::mock('Symfony\\Component\\Routing\\Router');
        $mockRouter->shouldIgnoreMissing();
        $mockRouter->shouldReceive('generate')->andReturn('/file.php/somecode/index.jpg');
        App::$container->shouldReceive('get')->with('router')->andReturn($mockRouter);
        App::$container->shouldReceive('generateStaticSecurityToken')->andReturn('abcd');

        $blob = new Blob();
        $blob->setContentType('image/jpg');
        $blob->setAuthCode($authcode);
        $blob->setFilename('image.jpeg');

        // WHEN
        $url         = $blob->getThumbnailUrl(50, UrlGeneratorInterface::RELATIVE_PATH);
        $urlAbsolute = $blob->getThumbnailUrl(50, UrlGeneratorInterface::ABSOLUTE_PATH);

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
}
