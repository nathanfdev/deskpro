<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\RowNode;

class Twig_Tests_Node_RowTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $node = new RowNode(['body' => $body], []);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests   = [];
        $body    = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $tests[] = [new RowNode(['body' => $body], []), <<<'EOF'
$context['zurb_columns']['first'] = true;
// line 1
echo "<table class=\"row\">";
echo "<tbody>";
echo "<tr>";
echo (isset($context["foo"]) ? $context["foo"] : null);
echo "</tr>";
echo "</tbody>";
echo "</table>";
unset($context['zurb_columns']);
EOF
    ,
        ];

        return $tests;
    }
}
