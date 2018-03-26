<?php

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
