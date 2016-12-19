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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\Node;

class GroupNode extends \Twig_Node
{
    /**
     * @var GroupItemNode[]
     */
    private $items = [];

    /**
     * @param GroupItemNode $node
     */
    public function addGroupItem(GroupItemNode $node)
    {
        $this->items[$node->getId()] = $node;
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        /* @var \Twig_Node $node */
        if ($this->hasNode('values')) {
            foreach ($this->getNode('values') as $sub_node) {
                if ($sub_node instanceof GroupItemNode && isset($this->items[$sub_node->getId()])) {
                    $compiler->subcompile($sub_node);
                }
            }
        }

        $compiler->write('$context[\'group\'] = array(\'count\' => 0, \'any\' => false, \'empty\' => true, \'items\' => array());');
        $compiler->write("\n");

        foreach ($this->items as $item) {
            $compiler->write(sprintf("\$context['group']['items']['%s'] = null;\n", $item->getId()));
            $compiler->write(sprintf(
                "if (isset(\$%s) && \$%s !== '') { \$context['group']['count']++; \$context['group']['items']['%s'] = \$%s; }\n",
                $item->getInternalVar(),
                $item->getInternalVar(),
                $item->getId(),
                $item->getInternalVar()
            ));
        }
        $compiler->write("if (\$context['group']['count'] !== 0) { \$context['group']['any'] = true; \$context['group']['empty'] != false; }\n\n");

        $compiler->write('if ($context[\'group\'][\'count\'] >= ');
        if ($this->hasNode('min')) {
            $compiler->subcompile($this->getNode('min'));
        } else {
            $compiler->raw('1');
        }
        if ($this->hasAttribute('condition')) {
            $compiler->raw('&&');
            $compiler->subcompile($this->getNode('condition'));
        }
        $compiler->raw(") {\n");
        $compiler->indent();

        if ($this->hasNode('values')) {
            foreach ($this->getNode('values') as $sub_node) {
                if ($sub_node instanceof GroupItemNode && isset($this->items[$sub_node->getId()])) {
                    $compiler->write(sprintf(
                        "if (isset(\$%s) && \$%s !== '') echo \$%s;\n",
                        $sub_node->getInternalVar(),
                        $sub_node->getInternalVar(),
                        $sub_node->getInternalVar()
                    ));
                } else {
                    $compiler->subcompile($sub_node);
                }
            }
        }

        $compiler->write("}\n");
        $compiler->outdent();
    }
}
