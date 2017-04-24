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

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Feedback;

use Application\DeskPRO\Entity\CustomDataFeedback;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionParamsType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackMassActionParamsType.
 */
class FeedbackMassActionParamsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FeedbackStatusCategory
     */
    private $defaultStatusCategory;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em                    = $em;
        $this->defaultStatusCategory = $this->em->getRepository(FeedbackStatusCategory::class)->findOneBy(
            ['status_type' => Feedback::STATUS_ACTIVE],
            ['display_order' => 'ASC']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('set_type', EntityType::class, [
                'class'         => FeedbackCategory::class,
                'property_path' => 'category',
            ])
            ->add('set_status_category', EntityType::class, [
                'class'         => FeedbackStatusCategory::class,
                'property_path' => 'status_category',
            ])
            ->add('set_category', EntityType::class, [
                'class'         => CustomDefFeedback::class,
                'mapped'        => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('c')
                        ->join('c.parent', 'p')
                        ->where('p.sys_name = :sys_name')
                        ->setParameter('sys_name', 'cat')
                    ;
                },
            ])
            ->add('set_hidden_status', ChoiceType::class, [
                'property_path'     => 'hidden_status',
                'choices_as_values' => true,
                'choices'           => [
                    Feedback::HIDDEN_STATUS_DELETED,
                    Feedback::HIDDEN_STATUS_DRAFT,
                    Feedback::HIDDEN_STATUS_SPAM,
                    Feedback::HIDDEN_STATUS_UNPUBLISHED,
                ],
            ])
            ->add('add_labels', CollectionType::class, [
                'entry_type'     => TextType::class,
                'mapped'         => false,
                'error_bubbling' => false,
                'allow_add'      => true,
                'allow_delete'   => true,
            ])
            ->add('remove_labels', CollectionType::class, [
                'entry_type'     => TextType::class,
                'mapped'         => false,
                'error_bubbling' => false,
                'allow_add'      => true,
                'allow_delete'   => true,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'actions'    => ['approve', 'delete'],
            'data_class' => Feedback::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionParamsType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof Feedback) {
            return;
        }

        // labels
        $addLabels    = $form->get('add_labels')->getData();
        $removeLabels = $form->get('remove_labels')->getData();

        if (is_array($addLabels)) {
            foreach ($addLabels as $string) {
                $data->addLabelByString($string);
            }
        }

        if (is_array($removeLabels)) {
            foreach ($removeLabels as $string) {
                $label = $data->findLabelByString($string);
                if ($label) {
                    $data->getLabels()->removeElement($label);
                }
            }
        }

        // category
        /** @var CustomDefFeedback $categoryChoice */
        $categoryChoice = $form->get('set_category')->getData();
        if ($categoryChoice) {
            $categoryDef = $categoryChoice->getParent();
            if ($categoryDef) {
                /** @var CustomDataFeedback[]|ArrayCollection $customData */
                $customData = $data->getCustomData()->filter(function (CustomDataFeedback $customData) use ($categoryDef) {
                    return $customData->getRootField() === $categoryDef;
                });

                /* @var CustomDataFeedback $item */
                if ($customData->count()) {
                    $item = $customData->first();
                } else {
                    $item = $categoryDef->createCustomData();
                    $data->addCustomData($item);
                }

                $item->setRootField($categoryDef);
                $item->setField($categoryChoice);
                $item->setValue(1);
            }
        }

        // actions
        if (BaseMassActionParamsType::hasAction($event, 'approve')) {
            if ($data->getStatus() === Feedback::STATUS_HIDDEN) {
                $data
                    ->setHiddenStatus()
                    ->setStatus(Feedback::STATUS_ACTIVE)
                    ->setStatusCategory($this->defaultStatusCategory)
                ;
            }

            $data->setIsReviewed(true);
        }

        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $data
                ->setStatus(Feedback::STATUS_HIDDEN)
                ->setHiddenStatus(Feedback::HIDDEN_STATUS_DELETED)
                ->setIsReviewed(true)
            ;
        }
    }
}
