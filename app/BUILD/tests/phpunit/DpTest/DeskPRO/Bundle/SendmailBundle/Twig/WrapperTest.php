<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\WrapperNode;

class Twig_Tests_Node_WrapperTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $node = new WrapperNode(['body' => $body], []);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests   = [];
        $body    = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $tests[] = [new WrapperNode(['body' => $body], []), <<<'EOF'
// line 1
echo "<table class=\"wrapper\" align=\"center\">";
echo "<tr>";
echo "<td class=\"wrapper-inner\">";
echo ($context["foo"] ?? null);
echo "</td>";
echo "</tr>";
echo "</table>";
EOF
    ,
        ];
        $tests[] = [new WrapperNode(['body' => $body, 'class' => 'header', 'bgcolor' => '#8a8a8a'], []), <<<'EOF'
// line 1
echo "<table bgcolor=\"#8a8a8a\" class=\"wrapper header\" align=\"center\">";
echo "<tr>";
echo "<td class=\"wrapper-inner\">";
echo ($context["foo"] ?? null);
echo "</td>";
echo "</tr>";
echo "</table>";
EOF
    ,
        ];

        return $tests;
    }
}
