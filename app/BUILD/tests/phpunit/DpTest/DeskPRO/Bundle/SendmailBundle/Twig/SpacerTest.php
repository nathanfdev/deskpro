<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
        $tests[] = [new SpacerNode(['size' => new Twig_Node_Print(new Twig_Node_Expression_Constant(50, 1), 1)], []),
                    <<<'EOF'
// line 1
echo "<table class=\"spacer\">";
echo "<tbody>";
echo "<tr>";
echo "<td height=\"";
echo 50;
echo "px\" style=\"font-size:";
echo 50;
echo "px;line-height:";
echo 50;
echo "px;\">&#xA0;</td>";
echo "</tr>";
echo "</tbody>";
echo "</table>";
EOF
        ];

        return $tests;
    }
}
