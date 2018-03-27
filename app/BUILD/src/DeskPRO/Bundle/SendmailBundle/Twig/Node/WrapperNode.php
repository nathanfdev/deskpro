<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\Node;

use Twig_Node_Expression_Constant;
use Twig_Node_Print;

class WrapperNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $class = '';
        if ($this->hasNode('class')) {
            $class = ' '.$this->getNode('class');
        }
        $bgColor = '';
        if ($this->hasNode('bgcolor')) {
            $bgColor = 'bgcolor="'.$this->getNode('bgcolor').'" ';
        }
        $compiler
            ->addDebugInfo($this)
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<table '.$bgColor.'class="wrapper'.$class.'" align="center">', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('<td class="wrapper-inner">', 0), 1))
            ->subcompile($this->getNode('body'))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</td>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</tr>', 0), 1))
            ->subcompile(new Twig_Node_Print(new Twig_Node_Expression_Constant('</table>', 0), 1))
        ;
    }
}
