<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\CustomDataChat as CustomDataChatRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom ticket data.
 */
class CustomDataChat extends CustomDataAbstract
{
    /**
     * The conversation the message belongs to.
     *
     * @var ChatConversation
     */
    protected $conversation;

    /**
     * @var CustomDefChat
     */
    protected $field;

    /**
     * @var CustomDefChat
     */
    protected $root_field;

    /**
     * @return ChatConversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @param ChatConversation $conversation
     *
     * @return $this
     */
    public function setConversation($conversation)
    {
        $this->setModelField('conversation', $conversation);

        return $this;
    }

    public function getConversationId()
    {
        return $this->conversation->getId();
    }

    /**
     * {@inheritdoc}
     *
     * @return ChatConversation
     */
    public function getOwner()
    {
        return $this->conversation;
    }

    /**
     * Set a field.
     *
     * @param CustomDefChat $field
     *
     * @return $this
     */
    public function setField(CustomDefChat $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefChat $field
     *
     * @return $this
     */
    public function setRootField(CustomDefChat $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = CustomDataChatRepository::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_chat',
                'uniqueConstraints' => [],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value',
                'type'       => 'bigint',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'conversation',
                'targetEntity' => ChatConversation::class,
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'conversation_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'field',
                'targetEntity' => CustomDefChat::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'root_field',
                'targetEntity' => CustomDefChat::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
