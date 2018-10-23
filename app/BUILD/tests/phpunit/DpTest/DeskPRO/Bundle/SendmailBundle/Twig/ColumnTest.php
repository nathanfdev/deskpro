<?php

use DeskPRO\Bundle\SendmailBundle\Twig\Node\ColumnNode;

class Twig_Tests_Node_ColumnTest extends Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $node = new ColumnNode(['body' => $body], []);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests   = [];
        $body    = new Twig_Node([new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)], [], 1);
        $tests[] = [new ColumnNode(['body' => $body], []), <<<'EOF'
$className = '';
$className .= 'columns';
if ($context['zurb_columns']['first']) {
    $className .= ' first';
}
echo "<th class=\"$className\">";
// line 1
echo "<table>";
echo "<tr>";
echo "<th>";
echo ($context["foo"] ?? null);
echo "</th>";
echo "</tr>";
echo "</table>";
echo "</th>";
$context['zurb_columns']['first'] = false;
EOF
    ,
        ];

        return $tests;
    }
}
