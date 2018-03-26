<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class ColumnNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler->write("\$className = '';\n");
        if ($this->hasNode('className')) {
            $compiler->write("\$className .= '".$this->getNode('className')->getAttribute('value')." ';\n");
        }
        if ($this->hasNode('small')) {
            $compiler->write("\$className .= 'small-".$this->getNode('small')->getAttribute('value')." ';\n");
        }
        if ($this->hasNode('large')) {
            $compiler->write("\$className .= 'large-".$this->getNode('large')->getAttribute('value')." ';\n");
        }
        $compiler->write("\$className .= 'columns';\n")
            ->write("if (\$context['zurb_columns']['first']) {\n")
            ->indent()
            ->write("\$className .= ' first';\n")
            ->outdent()
            ->write("}\n")
        ;
        $compiler
            ->write('echo "<th class=\"$className\">";'."\n")
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<th>', 0), 1))
            ->subcompile($this->getNode('body'))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</th>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</th>', 0), 1))
        ;
        $compiler->write("\$context['zurb_columns']['first'] = false;\n");
    }
}
