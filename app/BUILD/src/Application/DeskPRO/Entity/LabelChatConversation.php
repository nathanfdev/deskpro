<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
     * Labels on chats.
     */
    class LabelChatConversation extends LabelAssocAbstract
    {
        const LABEL_TYPENAME = 'chat_conversations';

        /**
         * @var \Application\DeskPRO\Entity\ChatConversation
         */
        protected $chat;

        /**
         * @return ChatConversation
         */
        public function getChat()
        {
            return $this->chat;
        }

        /**
         * @param ChatConversation $chat
         *
         * @return $this
         */
        public function setChat(ChatConversation $chat = null)
        {
            $this->setModelField('chat', $chat);

            return $this;
        }

        //###########################################################################
        // Doctrine Metadata
        //###########################################################################

        public static function loadMetadata(ClassMetadata $metadata)
        {
            $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
            $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelChatConversation';
            $metadata->setPrimaryTable(
                [
                     'name'    => 'labels_chat_conversations',
                     'indexes' => [
                         'label_idx' => ['columns' => ['label']],
                     ],
                ]
            );
            $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
            $metadata->mapManyToOne(
                [
                     'fieldName'    => 'chat',
                     'id'           => true,
                     'targetEntity' => 'Application\\DeskPRO\\Entity\\ChatConversation',
                     'mappedBy'     => null,
                     'inversedBy'   => 'labels',
                     'joinColumns'  => [
                         [
                             'name'                 => 'chat_id',
                             'referencedColumnName' => 'id',
                             'nullable'             => true,
                             'onDelete'             => 'cascade',
                             'columnDefinition'     => null,
                         ],
                     ],
                ]
            );
            $metadata->mapField(
                [
                     'fieldName'  => 'label',
                     'type'       => 'string',
                     'length'     => 255,
                     'precision'  => 0,
                     'scale'      => 0,
                     'nullable'   => false,
                     'columnName' => 'label',
                     'id'         => true,
                ]
            );
        }
    }
