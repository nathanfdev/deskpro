<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class SpacerNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $size = $this->getNode('size')->getAttribute('value');
        $compiler
            ->addDebugInfo($this)
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table class="spacer">', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant(
                '<td height="'.$size.'px" style="font-size:'.$size.'px;line-height:'.$size.'px;">&#xA0;</td>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
        ;
    }
}
