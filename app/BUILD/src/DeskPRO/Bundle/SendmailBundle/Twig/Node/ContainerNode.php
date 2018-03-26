<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class ContainerNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table align="center" class="container">', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<td>', 0), 1))
            ->subcompile($this->getNode('body'))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</td>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
        ;
    }
}
