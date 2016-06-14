<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpUnitTests\DeskPRO\Templating\Templates;

use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Orb\Util\Strings;

class EmailTemplateCodeTest extends \DpUnitTestCase
{
    public function testSimple()
    {
        $email_code = new EmailTemplateCode($this->getSimpleCode());

        $this->assertEquals($email_code->getSubject(), '');
        $this->assertEquals($email_code->getBody(), 'This is a template with no subject.');
    }

    public function testEmail()
    {
        $email_code = new EmailTemplateCode($this->getEmailCode());

        $this->assertEquals($email_code->getSubject(), 'This is the subject');
        $this->assertEquals($email_code->getBody(), 'This is the body.');
    }

    public function testMultiline()
    {
        $email_code = new EmailTemplateCode($this->getMultilineCode());

        $subj = Strings::implodeLines($email_code->getSubject());
        $body = Strings::implodeLines($email_code->getBody());

        $this->assertEquals($subj, 'This is a multi-line subject.');
        $this->assertEquals($body, 'This is a multi-line body.');
    }

    public function testMixed()
    {
        $email_code = new EmailTemplateCode($this->getMixedContentCode());

        $subj = Strings::implodeLines($email_code->getSubject());
        $body = Strings::implodeLines($email_code->getBody());

        $this->assertEquals($subj, 'This is the subject.');
        $this->assertEquals($body, 'This is a body with subject in the middle.');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidCodeOrder()
    {
        $email_code = new EmailTemplateCode($this->getInvalidCode());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidCodeMissing()
    {
        $email_code = new EmailTemplateCode($this->getInvalidCode2());
    }

    ####################################################################################################################

    private function getSimpleCode()
    {
        $code = <<<'STR'
This is a template with no subject.
STR;

        return $code;
    }

    private function getEmailCode()
    {
        $code = <<<'STR'
<dp:subject>This is the subject</dp:subject>
This is the body.
STR;

        return $code;
    }

    private function getMultilineCode()
    {
        $code = <<<'STR'
<dp:subject>
    This is a
    multi-line subject.
</dp:subject>
This is a
multi-line body.
STR;

        return $code;
    }

    private function getMixedContentCode()
    {
        $code = <<<'STR'
This is a body
<dp:subject>This is the subject.</dp:subject>
with subject in the middle.
STR;

        return $code;
    }

    private function getInvalidCode()
    {
        $code = <<<'STR'
</dp:subject>This is the subject.<dp:subject>
This is the body.
STR;

        return $code;
    }

    private function getInvalidCode2()
    {
        $code = <<<'STR'
<dp:subject>This is the subject.
This is the body.
STR;

        return $code;
    }
}
