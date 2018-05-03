<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class RowNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler->write("\$context['zurb_columns']['first'] = true;\n");
        $compiler
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table class="row">', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile($this->getNode('body'))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tbody>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
        ;
        $compiler->write("unset(\$context['zurb_columns']);\n");
    }
}
