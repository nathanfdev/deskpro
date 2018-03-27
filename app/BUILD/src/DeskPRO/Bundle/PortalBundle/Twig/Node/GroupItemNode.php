<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\Node;

class GroupItemNode extends \Twig_Node
{
    private static $count = 0;

    /**
     * @var string
     */
    private $id;

    public function __construct(array $nodes = [], array $attributes = [], $lineno = 0, $tag = null)
    {
        parent::__construct($nodes, $attributes, $lineno, $tag);

        ++self::$count;

        if ($this->hasAttribute('item_name') && $this->getAttribute('item_name')) {
            $this->id = $this->getAttribute('item_name');
        } else {
            $this->id = 'item'.self::$count;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getInternalVar()
    {
        return '_dp_gi_'.$this->id;
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        if ($this->hasNode('condition')) {
            $compiler->write('if (');
            $compiler->subcompile($this->getNode('condition'));
            $compiler->raw(") {\n");
            $compiler->indent();
        }

        $compiler->write('ob_start();');

        if ($this->hasNode('values')) {
            $compiler->subcompile($this->getNode('values'));
        }

        $compiler->write(sprintf("\$%s = trim(ob_get_clean());\n", $this->getInternalVar()));

        if ($this->hasNode('condition')) {
            $compiler->write("} else {\n");
            $compiler->write(sprintf("\t\$%s = null;\n", $this->getInternalVar()));
            $compiler->write("}\n");
            $compiler->outdent();
        }
    }
}
