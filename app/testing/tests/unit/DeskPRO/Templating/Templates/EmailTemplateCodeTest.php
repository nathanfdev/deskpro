<?php
namespace DpUnitTests\DeskPRO\Templating\Templates;

use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Orb\Util\Strings;

class EmailTemplateCodeTest extends \DpUnitTestCase
{
    public function testSimple()
    {
        $email_code = new EmailTemplateCode($this->getSimpleCode());

        $this->assertEquals($email_code->getSubject(), "");
        $this->assertEquals($email_code->getBody(), "This is a template with no subject.");
    }

    public function testEmail()
    {
        $email_code = new EmailTemplateCode($this->getEmailCode());

        $this->assertEquals($email_code->getSubject(), "This is the subject");
        $this->assertEquals($email_code->getBody(), "This is the body.");
    }

    public function testMultiline()
    {
        $email_code = new EmailTemplateCode($this->getMultilineCode());

        $subj = Strings::implodeLines($email_code->getSubject());
        $body = Strings::implodeLines($email_code->getBody());

        $this->assertEquals($subj, "This is a multi-line subject.");
        $this->assertEquals($body, "This is a multi-line body.");
    }

    public function testMixed()
    {
        $email_code = new EmailTemplateCode($this->getMixedContentCode());

        $subj = Strings::implodeLines($email_code->getSubject());
        $body = Strings::implodeLines($email_code->getBody());

        $this->assertEquals($subj, "This is the subject.");
        $this->assertEquals($body, "This is a body with subject in the middle.");
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
