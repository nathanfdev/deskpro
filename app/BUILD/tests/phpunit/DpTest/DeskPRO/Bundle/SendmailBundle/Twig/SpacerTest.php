<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\SpacerNode;

class Twig_Tests_Node_SpacerTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $node = new SpacerNode(['size' => 50], []);

        $this->assertEquals(50, $node->getNode('size'));
    }

    public function getTests()
    {
        $tests   = [];
        $tests[] = [new SpacerNode(['size' => new Twig_Node_Expression_Constant(50, 1)], []),
                    <<<'EOF'
// line 1
echo "<table class=\"spacer\">";
echo "<tbody>";
echo "<tr>";
echo "<td height=\"50px\" style=\"font-size:50px;line-height:50px;\">&#xA0;</td>";
echo "</tr>";
echo "</tbody>";
echo "</table>";
EOF
        ];

        return $tests;
    }
}
