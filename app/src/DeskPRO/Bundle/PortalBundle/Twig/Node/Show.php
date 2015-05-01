<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\Node;

class Show extends \Twig_Node
{
    /**
     * @param string $tag_name
     * @param bool $is_page_tag
     * @param int $line
     * @param null $tag
     */
    public function __constructx($tag_name, \Twig_Node_Expression $variables = null, $is_page_tag, $line, $tag = null)
    {
        parent::__construct(array(
            'variables' => $variables
        ), array(
            'tag_name' => $tag_name,
            'is_page_tag' => $is_page_tag
        ), $line, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf(
                'echo $this->env->getExtension(\'%s\')->%s',
                $this->getAttribute('ext_name'),
                $this->getAttribute('is_page_tag') ? 'processPortalPageTag' : 'processPortalTag'
            ))
            ->write('(')
            ->write('$context')
            ->write(', ')
            ->repr($this->getAttribute('tag_name'))
            ->write(', ');

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'));
        } else {
            $compiler->write('array()');
        }

        $compiler->write(')')->raw(";\n");
    }
}