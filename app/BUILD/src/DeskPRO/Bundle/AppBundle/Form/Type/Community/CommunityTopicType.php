<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Community;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\LabelCommunityTopic;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\CommunityForumType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CommunityTopicType.
 */
class CommunityTopicType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(CustomFieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
            ])
            ->add('person', PersonAssignType::class, [
                'person'   => $options['person'],
                'required' => false,
            ])
            ->add('forum', CommunityForumType::class, [
                'person'   => $options['person'],
                'required' => true,
            ])
            ->add('status', ChoiceType::class, [
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    CommunityTopic::STATUS_ACTIVE,
                    CommunityTopic::STATUS_CLOSED,
                    CommunityTopic::STATUS_HIDDEN,
                ],
            ])
            ->add('labels', LabelsCollectionType::class, [
                'required'       => false,
                'labels_class'   => LabelCommunityTopic::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'topic',
            ])
            ->add('fields', CombinedType::class, [
                'required'       => false,
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
                'fields_group'   => true,
            ])
            ->add('is_reviewed', ApiBooleanType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetStatus']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class'      => CommunityTopic::class,
                'agent_interface' => false,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetStatus(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!isset($data['status'])) {
            return;
        }

        if (in_array($data['status'], [CommunityTopic::STATUS_ACTIVE, CommunityTopic::STATUS_CLOSED])) {
            $form->add('status_category', EntityType::class, [
                'required'      => true,
                'class'         => CommunityTopicStatusCategory::class,
                'query_builder' => function (EntityRepository $er) use ($data) {
                    return $er
                        ->createQueryBuilder('c')
                        ->where('c.status_type = :status_type')
                        ->setParameter('status_type', $data['status'])
                    ;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);

            // force set status category if not exist
            if (!isset($data['status_category'])) {
                $data['status_category'] = null;
            }
        } elseif ($data['status'] === CommunityTopic::STATUS_HIDDEN) {
            $form->add('hidden_status', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    CommunityTopic::STATUS_PUBLISHED,
                    CommunityTopic::STATUS_ARCHIVED,
                    CommunityTopic::STATUS_HIDDEN,
                    CommunityTopic::HIDDEN_STATUS_UNPUBLISHED,
                    CommunityTopic::HIDDEN_STATUS_DELETED,
                    CommunityTopic::HIDDEN_STATUS_SPAM,
                    CommunityTopic::HIDDEN_STATUS_DRAFT,
                    CommunityTopic::HIDDEN_STATUS_PENDING,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);

            // force set hidden status if not exist
            if (!isset($data['hidden_status'])) {
                $data['hidden_status'] = null;
            }
        }

        $event->setData($data);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $defs   = $this->fieldManager->getAvailableCommunityDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'inline'          => true,
                ],
            ];
        }

        return $fields;
    }
}
