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
