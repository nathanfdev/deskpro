<?php

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
