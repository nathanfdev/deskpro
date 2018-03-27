<?php

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
    public function createModel($entity, SideloadSerializationContext $context)
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
