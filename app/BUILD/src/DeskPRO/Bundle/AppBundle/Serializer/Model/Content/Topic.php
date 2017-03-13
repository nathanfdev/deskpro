<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Topic as TopicEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Topic.
 */
class Topic extends ContentAbstract
{
    /**
     * Display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("product")
     *
     * @var int
     */
    protected $displayOrder = 0;

    /**
     * Topic's parent.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Topic>")
     *
     * @var Topic
     */
    protected $parent;

    /**
     * Topic's children.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Topic>>")
     *
     * @var Topic[]
     */
    protected $children;

    /**
     * Constructor.
     *
     * @param TopicEntity $entity
     */
    public function __construct(TopicEntity $entity)
    {
        parent::__construct($entity);
        $this->person       = null;
        $this->children     = $entity->getChildren();
        $this->displayOrder = $entity->getDisplayOrder();
        $this->parent       = $entity->getParent();
    }
}
