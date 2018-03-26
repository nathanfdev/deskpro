<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\CalloutNode;

class Twig_Tests_Node_CalloutTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $node = new CalloutNode(['body' => $body], []);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests   = [];
        $body    = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $tests[] = [new CalloutNode(['body' => $body], []), <<<'EOF'
// line 1
echo "<table class=\"callout\">";
echo "<tr>";
echo "<th class=\"callout-inner\">";
echo (isset($context["foo"]) ? $context["foo"] : null);
echo "</th>";
echo "<th class=\"expander\"></th>";
echo "</tr>";
echo "</table>";
EOF
    ,
        ];

        return $tests;
    }
}
