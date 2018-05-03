<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class CalloutNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $class = '';
        if ($this->hasNode('class')) {
            $class = ' '.$this->getNode('class')->getAttribute('value');
        }
        $compiler
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table class="callout">', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<th class="callout-inner'.$class.'">', 0), 1))
            ->subcompile($this->getNode('body'))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</th>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<th class="expander"></th>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
        ;
    }
}
