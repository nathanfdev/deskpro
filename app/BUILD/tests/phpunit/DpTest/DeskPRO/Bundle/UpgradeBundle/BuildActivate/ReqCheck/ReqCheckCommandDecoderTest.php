<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckCommandDecoder;
use DpTest\DeskProTestCase;

class ReqCheckCommandDecoderTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_decodes_correctly()
    {
        $json = <<<'JSON'
{
    "failed_requirements": [
        {
            "description": "PHP version must be at least 7.5.0 (5.6.14 installed)",
            "help": "Install PHP 7.5.0 or newer (installed version is 5.6.14)"
        }
    ],
    "failed_recommendations": [
        {
            "description": "IMAP should be installed",
            "help": "Install and enable the IMAP extension. This is required if you want to read email from IMAP email servers."
        },
        {
            "description": "intl should be installed",
            "help": "Install and enable the intl extension (used for validators)."
        }
    ]
}
JSON;

        $out = "foo bar \n\n -------------------------BEGIN-------------------------\n"
            .$json
            ."-------------------------END-------------------------foo bar\n";

        $decoder = new ReqCheckCommandDecoder();
        $res     = $decoder->decodeResults($out);

        $this->assertEquals(json_decode($json, true), $res);
    }

    /**
     * @test
     */
    public function it_decodes_correctly_with_no_errors()
    {
        $json = <<<'JSON'
{
    "failed_requirements": [],
    "failed_recommendations": []
}
JSON;

        $out = "foo bar \n\n -------------------------BEGIN-------------------------\n"
            .$json
            ."-------------------------END-------------------------foo bar\n";

        $decoder = new ReqCheckCommandDecoder();
        $res     = $decoder->decodeResults($out);

        $this->assertEquals(json_decode($json, true), $res);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException
     * @expectedExceptionCode \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException::CMD_ERROR
     */
    public function it_requires_known_shape()
    {
        $json = <<<'JSON'
{
    "foo": 123,
    "bar": [null]
}
JSON;

        $out = "-------------------------BEGIN-------------------------\n"
            .$json
            ."-------------------------END-------------------------\n";

        $decoder = new ReqCheckCommandDecoder();
        $decoder->decodeResults($out);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException
     * @expectedExceptionCode \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException::CMD_ERROR
     */
    public function it_requires_known_shape2()
    {
        $json = '{}';

        $out = "-------------------------BEGIN-------------------------\n"
            .$json
            ."-------------------------END-------------------------\n";

        $decoder = new ReqCheckCommandDecoder();
        $decoder->decodeResults($out);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException
     * @expectedExceptionCode \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException::CMD_ERROR
     */
    public function it_requires_valid_json()
    {
        $json = <<<'JSON'
this is totally invalid json
JSON;

        $out = "-------------------------BEGIN-------------------------\n"
            .$json
            ."-------------------------END-------------------------\n";

        $decoder = new ReqCheckCommandDecoder();
        $decoder->decodeResults($out);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException
     * @expectedExceptionCode \DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException::CMD_ERROR
     */
    public function it_requires_delims()
    {
        $json = <<<'JSON'
{
    "failed_requirements": [
        {
            "description": "PHP version must be at least 7.5.0 (5.6.14 installed)",
            "help": "Install PHP 7.5.0 or newer (installed version is 5.6.14)"
        }
    ],
    "failed_recommendations": [
        {
            "description": "IMAP should be installed",
            "help": "Install and enable the IMAP extension. This is required if you want to read email from IMAP email servers."
        },
        {
            "description": "intl should be installed",
            "help": "Install and enable the intl extension (used for validators)."
        }
    ]
}
JSON;

        $out = "-------------------------INVALID-------------------------\n"
            .$json
            ."-------------------------DELIMS-------------------------\n";

        $decoder = new ReqCheckCommandDecoder();
        $decoder->decodeResults($out);
    }
}
