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

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\LabelChatConversation;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\Chat;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\WidgetChat;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class ChatConversationHandler.
 */
class ChatConversationHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $chatIds = [];

    /**
     * @var array
     */
    private $labels;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param ChatConversation $entity
     */
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $this->chatIds[] = $entity->getId();
        $serializerClass = $context->getMappedClass(ChatConversation::class);

        if ($serializerClass === ChatCsv::class) {
            return new ChatCsv($entity);
        } elseif ($serializerClass === WidgetChat::class) {
            return new WidgetChat($entity);
        }

        $model = new Chat($entity);
        $model->setLabels(new CallbackDeferredProperty([$this, 'getLabels'], [$entity]));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ChatConversation::class;
    }

    /**
     * @param ChatConversation $entity
     *
     * @return LabelChatConversation[]
     */
    public function getLabels(ChatConversation $entity)
    {
        if (null === $this->labels) {
            $result = $this->em->getRepository(LabelChatConversation::class)->findBy([
                'chat' => $this->chatIds,
            ]);

            $this->labels = [];
            foreach ($result as $label) {
                $this->labels[$label->getChat()->getId()][] = $label;
            }
        }

        if (isset($this->labels[$entity->getId()])) {
            return $this->labels[$entity->getId()];
        }

        return [];
    }
}
