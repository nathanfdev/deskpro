<?php

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
