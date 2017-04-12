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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\TextSnippets;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippet as TextSnippetEntity;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\TextSnippets\TextSnippet as TextSnippetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Twig\TwigTemplateRenderer;
use Doctrine\ORM\EntityManager;

/**
 * Class TextSnippetHandler.
 */
class TextSnippetHandler extends AbstractEntityHandler
{
    /**
     * @var TwigTemplateRenderer
     */
    private $templateRenderer;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TextSnippetHandler constructor.
     *
     * @param TwigTemplateRenderer $templateRenderer
     * @param EntityManager        $em
     */
    public function __construct(TwigTemplateRenderer $templateRenderer, EntityManager $em)
    {
        $this->templateRenderer = $templateRenderer;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TextSnippetEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TextSnippetEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        /** @var Person $user */
        $user  = $context->getUser();
        $title = $entity->getObjectPropLanguageTranslationValue('title', $user->getLanguage());
        $model = new TextSnippetModel($entity, $title);

        if ($entity->getCategory()) {
            $sideloads = $context->getSideloadStore();
            $sideloads->addCustomSideload(
                'text_snippet_content',
                $entity->getId(),
                new CallbackDeferredProperty([$this, 'getSnippetContent'], [$entity, $context]),
                $model
            );
        }

        return $model;
    }

    /**
     * @param TextSnippetEntity            $entity
     * @param SideloadSerializationContext $context
     *
     * @return array|null
     */
    public function getSnippetContent(TextSnippetEntity $entity, SideloadSerializationContext $context)
    {
        $contents = $entity->getTextSnippetContents();
        $category = $entity->getCategory();

        if ($category->getTypename() === TextSnippetCategory::TYPE_TICKET) {
            $entityType  = 'ticket';
            $entityClass = Ticket::class;
        } else {
            $entityType  = 'chat';
            $entityClass = ChatConversation::class;
        }

        // if related entity id was provided then we need to process the text replacements
        $contextEntityId = $context->getRequest()->query->getInt($entityType);
        if ($contextEntityId) {
            $contextParams[$entityType] = $this->em->getRepository($entityClass)->find($contextEntityId)->toApiData();

            foreach ($contents as $content) {
                $content
                    ->setTitle($this->templateRenderer->renderStringTemplate($content->getTitle(), $contextParams))
                    ->setContent($this->templateRenderer->renderStringTemplate($content->getContent(), $contextParams))
                ;
            }
        }

        return $context->accept($contents ?: null);
    }
}
