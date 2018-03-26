<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\ContainerNode;

class Twig_Tests_Node_ContainerTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $node = new ContainerNode(['body' => $body], []);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests   = [];
        $body    = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $tests[] = [new ContainerNode(['body' => $body], []), <<<'EOF'
// line 1
echo "<table align=\"center\" class=\"container\">";
echo "<tbody>";
echo "<tr>";
echo "<td>";
echo (isset($context["foo"]) ? $context["foo"] : null);
echo "</td>";
echo "</tr>";
echo "</tbody>";
echo "</table>";
EOF
    ,
        ];

        return $tests;
    }
}
