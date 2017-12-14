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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDef as CustomDefModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefTranslation;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class CustomDefHandler.
 */
class CustomDefHandler extends AbstractEntityHandler
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Language[]
     */
    private $languages;

    /**
     * Constructor.
     *
     * @param Translate     $translate
     * @param EntityManager $em
     */
    public function __construct(Translate $translate, EntityManager $em)
    {
        $this->translate = $translate;
        $this->em        = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return [
            CustomDefTicket::class,
            CustomDefChat::class,
            CustomDefPerson::class,
            CustomDefOrganization::class,
            CustomDefArticle::class,
            CustomDefFeedback::class,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param CustomDefAbstract $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model        = new CustomDefModel($entity);
        $translations = [];

        if (null === $this->languages) {
            $this->languages = $this->em->getRepository(Language::class)->findAll();
        }

        foreach ($this->languages as $language) {
            $translations[$language->getId()] = new CustomDefTranslation(
                $this->translate->getPhraseObject($entity, 'title', $language),
                $this->translate->getPhraseObject($entity, 'description', $language),
                $language
            );
        }

        $model->setTranslations($translations);

        return $model;
    }
}
