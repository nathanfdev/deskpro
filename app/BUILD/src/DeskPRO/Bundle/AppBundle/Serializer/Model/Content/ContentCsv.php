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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ContentAbstract as ContentEntity;
use JMS\Serializer\Annotation as JMS;

class ContentCsv extends Content
{
    /**
     * Person created this content first time.
     *
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * Person created this content first time.
     *
     * @JMS\Type("string")
     */
    protected $language;

    /**
     * Content category.
     *
     * @JMS\Type("string")
     */
    protected $category;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\ContentAbstract $entity
     */
    public function __construct(ContentEntity $entity)
    {
        parent::__construct($entity);

        $this->person   = $entity->getPerson() ? $entity->getPerson()->getName() : '';
        $this->language = $entity->getLanguage() ? $entity->getLanguage()->getTitle() : '';
        if ($entity instanceof Article) {
            $categoryNames = [];
            $categories    = $entity->getCategories();
            foreach ($categories as $category) {
                $categoryNames[] = $category->getTitle();
            }
            $this->category = implode(', ', $categoryNames);
        } else {
            $this->category = $entity->getCategory() ? $entity->getCategory()->getTitle() : '';
        }
        $this->content = mb_substr($entity->getContentPlain(), 0, 50);
    }
}
