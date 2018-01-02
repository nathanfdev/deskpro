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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\LabelFeedback;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\FeedbackCategoryType;
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
 * Class FeedbackType.
 */
class FeedbackType extends AbstractType
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
            ->add('category', FeedbackCategoryType::class, [
                'person'   => $options['person'],
                'required' => true,
            ])
            ->add('status', ChoiceType::class, [
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    Feedback::STATUS_ACTIVE,
                    Feedback::STATUS_CLOSED,
                    Feedback::STATUS_HIDDEN,
                ],
            ])
            ->add('labels', LabelsCollectionType::class, [
                'required'       => false,
                'labels_class'   => LabelFeedback::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'feedback',
            ])
            ->add('fields', CombinedType::class, [
                'required'       => false,
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
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
                'data_class'      => Feedback::class,
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

        if (in_array($data['status'], [Feedback::STATUS_ACTIVE, Feedback::STATUS_CLOSED])) {
            $form->add('status_category', EntityType::class, [
                'required'      => true,
                'class'         => FeedbackStatusCategory::class,
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
        } elseif ($data['status'] === Feedback::STATUS_HIDDEN) {
            $form->add('hidden_status', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    Feedback::STATUS_PUBLISHED,
                    Feedback::STATUS_ARCHIVED,
                    Feedback::STATUS_HIDDEN,
                    Feedback::HIDDEN_STATUS_UNPUBLISHED,
                    Feedback::HIDDEN_STATUS_DELETED,
                    Feedback::HIDDEN_STATUS_SPAM,
                    Feedback::HIDDEN_STATUS_DRAFT,
                    Feedback::HIDDEN_STATUS_PENDING,
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
        $defs   = $this->fieldManager->getAvailableFeedbackDefs();
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
