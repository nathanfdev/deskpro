<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\LanguageBundle\Routing;

use DeskPRO\Bundle\PortalBundle\Routing\UrlMatcher;
use DpTest\DeskProTestCase;

class UrlMatcherTest extends DeskProTestCase
{
    public function testExtractsLanguage()
    {
        $matcher = new UrlMatcher();

        $this->assertEquals(
            ['lang_url_code' => 'en', 'remaining_pathinfo' => '/kb/articles/article-five'],
            $matcher->extractLanguageCode('/en/kb/articles/article-five')
        );

        $this->assertEquals(
            ['lang_url_code' => 'en', 'remaining_pathinfo' => '/'],
            $matcher->extractLanguageCode('/en')
        );

        $this->assertEquals(
            ['lang_url_code' => 'en', 'remaining_pathinfo' => '/'],
            $matcher->extractLanguageCode('/en/')
        );

        $this->assertEquals(
            ['lang_url_code' => null, 'remaining_pathinfo' => '/kb/articles/article-five'],
            $matcher->extractLanguageCode('/kb/articles/article-five')
        );
    }
}
