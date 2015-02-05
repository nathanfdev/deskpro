<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace DpUnitTests\EmailBundle\Mail\Decode;

use Application\EmailBundle\Mail\RawMessage\Rfc2822Decoder;

class MailDecodeTest extends \DpUnitTestCase
{
    public function testSimpleEmail()
    {
        $decoder = new Rfc2822Decoder();

        $fp = fopen(__DIR__.'/data/test_simple.eml', 'r');

        $m = $decoder->createRawMessage($fp);

        $this->assertEquals(
            array(array('email' => 'user@example.com', 'name' => 'User')),
            $m->getFrom(),
            'Check From'
        );

        $this->assertEquals(
            array(array('email' => 'target@example.com', 'name' => 'Target')),
            $m->getTos(),
            'Check To'
        );

        $this->assertEquals(
            'Test Message',
            $m->getSubject(),
            'Check Subject'
        );

        $this->assertEquals(
            'This is a simple email test. This is just text.',
            $m->getTextPart(),
            'Check text part'
        );

        $this->assertEquals(
            null,
            $m->getHtmlPart(),
            'Check HTML part'
        );

        $this->assertEquals(
            array(),
            $m->getAttachments(),
            'Check attachments'
        );
    }

    public function testSimpleEmailWithAttach()
    {
        $decoder = new Rfc2822Decoder();

        $fp = fopen(__DIR__.'/data/test_simple_with_attach.eml', 'r');

        $m = $decoder->createRawMessage($fp);

        $this->assertEquals(
            array(array('email' => 'user@example.com', 'name' => 'User')),
            $m->getFrom(),
            'Check From'
        );

        $this->assertEquals(
            array(array('email' => 'target@example.com', 'name' => 'Target')),
            $m->getTos(),
            'Check To'
        );

        $this->assertEquals(
            'Testing Simple with Attach',
            $m->getSubject(),
            'Check Subject'
        );

        $this->assertEquals(
            'Testing 123',
            $m->getTextPart(),
            'Check text part'
        );

        $this->assertEquals(
            null,
            $m->getHtmlPart(),
            'Check HTML part'
        );

        $attach = array_map(function($a) { unset($a['bin_data']); return $a; }, $m->getAttachments());
        $this->assertEquals(
            array(array(
                'filename' => 'text-file.txt',
                'cid'      => null,
                'type'     => 'application/octet-stream',
                'crc32'    => '1f28f50a'
            )),
            $attach,
            'Check attachments'
        );
    }

    public function testMultipartWithAttach()
    {
        $decoder = new Rfc2822Decoder();

        $fp = fopen(__DIR__.'/data/test_multipart_with_attach.eml', 'r');

        $m = $decoder->createRawMessage($fp);

        $this->assertEquals(
            array(array('email' => 'user@example.com', 'name' => 'User')),
            $m->getFrom(),
            'Check From'
        );

        $this->assertEquals(
            array(array('email' => 'target@example.com', 'name' => 'Target')),
            $m->getTos(),
            'Check To'
        );

        $this->assertEquals(
            'Test multi-part with attachments',
            $m->getSubject(),
            'Check Subject'
        );

        $this->assertEquals(
            "Test\n\n![](cid:51340020-E84B-44BF-A4DA-AEC6D2BA14F6@example.com \"super_man.gif\")",
            $m->getTextPart(),
            'Check text part'
        );

        $this->assertEquals(
            "<div class=3D\"markdown\">\n<p dir=3D\"auto\">Test</p>\n\n<p dir=3D\"auto\"><img src=3D\"cid:51340020-E84B-44BF-A4DA-AEC6D2BA14F6@xxxx=\nxxxxxxx.com\" alt=3D\"\" title=3D\"super_man.gif\"></p>\n\n</div>",
            $m->getHtmlPart(),
            'Check HTML part'
        );

        $attach = array_map(function($a) { unset($a['bin_data']); return $a; }, $m->getAttachments());
        $this->assertEquals(
            array(array(
                'filename' => 'super_man.gif',
                'cid'      => '<51340020-E84B-44BF-A4DA-AEC6D2BA14F6@xxxxxxxxxxx.com>',
                'type'     => 'image/gif',
                'crc32'    => 'd29df63c'
            )),
            $attach,
            'Check attachments'
        );
    }
}