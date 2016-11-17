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
