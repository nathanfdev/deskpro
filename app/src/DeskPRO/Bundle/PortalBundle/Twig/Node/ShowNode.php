<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Twig\Node;

/**
 * A show tag renders portal tags if it exists. If it does not exist,
 * then it will check to see if any template exists with a short name whereby we just 'include' it.
 *
 * To the end-user, the tags work in the same way: {% show something %}
 * But internally, we get to choose the best way to load the content. If tags are defined, then the whole
 * normal tag system is called (sub-requests, caching, esi etc). If it's just a 'show_tag_names', then
 * we just include it (which is faster) but still have the option of moving it to a real tag later.
 */
class ShowNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $ext_expr = '$this->env->getExtension(\''.$this->getAttribute('ext_name').'\')';

        $compiler->write(sprintf('if (%s->hasTag(', $ext_expr))->repr($this->getAttribute('tag_name'))->raw(")) {\n");
        $compiler->indent();

        $compiler->write(sprintf(
                'echo %s->%s',
                $ext_expr,
                $this->getAttribute('is_page_tag') ? 'processPortalPageTag' : 'processPortalTag'
            ))
            ->raw('(')
            ->raw('$context')
            ->raw(', ')
            ->repr($this->getAttribute('tag_name'))
            ->raw(', ');

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'));
        } else {
            $compiler->raw('array()');
        }

        $compiler->raw(')')->raw(";\n");

        $compiler->outdent();
        $compiler->write(sprintf('} elseif ($_dp_tag_tpl = %s->getTagIncludeTemplate(', $ext_expr))->repr($this->getAttribute('tag_name'))->raw(")) {\n");
        $compiler->indent();

        $compiler->write(sprintf('$this->env->loadTemplate($_dp_tag_tpl)->display('));

        if ($this->hasNode('variables')) {
            $compiler
                    ->raw('array_merge($context, ')
                    ->subcompile($this->getNode('variables'))
                    ->raw(')')
                ;
        } else {
            $compiler->raw('$context');
        }

        $compiler->raw(");\n");

        $compiler->outdent();
        $compiler->write(sprintf("} else { echo '[INVALID TAG: "))->string($this->getAttribute('tag_name'))->raw("]'; }\n");
    }
}
