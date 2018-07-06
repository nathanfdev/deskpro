<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CustomDefChoice.
 */
class CustomDefChoice
{
    /**
     * The unique ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var CustomDefAbstract
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefAbstract>")
     */
    private $parent;

    /**
     * The title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * Obviously it is field`s display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $displayOrder;

    /**
     * @var CustomDefAbstract[]
     *
     * @JMS\Type("collection<Application\DeskPRO\Entity\CustomDefAbstract>")
     */
    private $children;

    /**
     * @JMS\Type("map<DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefTranslation>")
     *
     * @var CustomDefTranslation[]
     */
    private $translations = [];

    /**
     * Constructor.
     *
     * @param CustomDefAbstract $def
     */
    public function __construct(CustomDefAbstract $def)
    {
        $this->id           = $def->getId();
        $this->parent       = $def->getParent();
        $this->title        = $def->getTitle();
        $this->displayOrder = $def->getDisplayOrder();

        if ($def->getParent()) {
            $this->children = $def->getParent()->getChildren()->filter(function (CustomDefAbstract $choice) use ($def) {
                return $choice->getOption('parent_id') === $def->getId();
            });
        } else {
            $this->children = new ArrayCollection();
        }
    }

    /**
     * @param CustomDefTranslation[] $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }
}
