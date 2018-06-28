<?php

namespace DpTest\DeskPRO\Bundle\SendmailBundle\Twig\PostRenderFilter;

use DeskPRO\Bundle\SendmailBundle\Twig\PostRenderFilter\EmailPostRenderFilter;
use DpTest\DeskProTestCase;

class EmailPostRenderFilterTest extends DeskProTestCase
{
    public function dataProvider_testProcess_Replace_P_by_DIV()
    {
        // CASE
        // <pre> tag should stay untouched
        // <p> should be replaced by <div>
        // chek <p> with spaces <p   >
        // check <p> with style <p class="abc" style="abc">

        // input
        $pre[] = <<< 'EOC'
<div dir="ltr" lang="en" name="dp_message_4798_begin" class="dp_message_4798_begin"><p>Code block:</p><pre class="dp-pre">foreach ($save_blocks as $id =&gt; $block) {
__DP_SPACE_PLACE__ __DP_SPACE_PLACE__ // Replace &lt;p&gt; with &lt;div /&gt; in messages
}
<br></pre><p  ><br></p><p>Quote block:</p><blockquote class="dp-bq">Simple quote</blockquote><p><br></p>

<p class="dp-signature-start" style="a-signature">Agent signature here!</p>

<p>Signature new line 1.</p>

<p>Signature new line 2.</p></div>
EOC;

        // expected output
        $pre[] = <<< 'EOC'
<div dir="ltr" lang="en" name="dp_message_4798_begin" class="dp_message_4798_begin"><div>Code block:</div><pre class="dp-pre">foreach ($save_blocks as $id =&gt; $block) {
__DP_SPACE_PLACE__ __DP_SPACE_PLACE__ // Replace &lt;p&gt; with &lt;div /&gt; in messages
}
<br></pre><div  ><br></div><div>Quote block:</div><blockquote class="dp-bq">Simple quote</blockquote><div><br></div>

<div class="dp-signature-start" style="a-signature">Agent signature here!</div>

<div>Signature new line 1.</div>

<div>Signature new line 2.</div></div>
EOC;

        return [
            $pre,
        ];
    }

    /**
     * @dataProvider dataProvider_testProcess_Replace_P_by_DIV
     *
     * @param string $input
     * @param string $expectedOutput
     */
    public function testProcess_Replace_P_by_DIV($input, $expectedOutput)
    {
        // GIVEN
        $filter = new EmailPostRenderFilter();

        // WHEN
        $output = $filter->process('DeskPRO:emails_user:ticket-reply.html.twig', $this->wrapMessage($input));

        // THEN
        $this->assertEquals($expectedOutput, $this->unwrapMessage($output), 'Wrong post render result');
    }

    /**
     *  Wrap message in Deskpro email message structure.
     *
     * @param string $message
     *
     * @return string
     */
    protected function wrapMessage($message)
    {
        $stub = file_get_contents(__DIR__.'/message_stub.html');

        return preg_replace(
            '#<!-- DP_MESSAGE_BEGIN -->(.*?)<!-- DP_MESSAGE_END -->#s',
            "<!-- DP_MESSAGE_BEGIN -->$message<!-- DP_MESSAGE_END -->",
            $stub
        );
    }

    /**
     * Extract message from Deskpro email message.
     *
     * @param string $message
     *
     * @return bool|string
     */
    protected function unwrapMessage($message)
    {
        $matches = [];
        if (preg_match('#<!-- DP_MESSAGE_BEGIN -->(.*?)<!-- DP_MESSAGE_END -->#s', $message, $matches)) {
            return $matches[1];
        }

        return false;
    }
}
