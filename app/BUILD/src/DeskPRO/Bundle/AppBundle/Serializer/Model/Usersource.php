<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Util;

/**
 * Class Usersource.
 */
class Usersource
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The title of this usersource.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * "user" or "agent" for now.
     *
     * the interface this usersource applies to
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $sourceType;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $displayType;

    /**
     * The order in which to display this source in UserBundle.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $displayOrder;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $displayOptions;

    /**
     * True if this usersource is enabled/usable.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isEnabled;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isAllBrands;

    /**
     * Brands.
     *
     * @JMS\Type("deferred<collection<entity<Application\DeskPRO\Entity\Brand>>>")
     *
     * @var \Application\DeskPRO\Entity\Brand[]
     */
    protected $brands;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\Usersource $usersource
     * @param string                                 $displayType
     * @param array                                  $displayOptions
     */
    public function __construct(\Application\DeskPRO\Entity\Usersource $usersource, $displayType, array $displayOptions)
    {
        $this->id             = $usersource->getId();
        $this->title          = $usersource->getTitle();
        $this->type           = $usersource->getType();
        $this->sourceType     = strtolower(Util::getBaseClassname($usersource->getSourceType()));
        $this->isEnabled      = $usersource->isEnabled();
        $this->displayOrder   = $usersource->getDisplayOrder();
        $this->displayType    = $displayType;
        $this->displayOptions = $displayOptions;
        $this->isAllBrands    = $usersource->isAllBrands();
    }

    /**
     * @param CallbackDeferredProperty $brands
     *
     * @return $this
     */
    public function setBrands($brands = null)
    {
        $this->brands = $brands;

        return $this;
    }
}
