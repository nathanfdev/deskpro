<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic;

use Application\DeskPRO\Entity\CommunityChannel;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\CustomDataCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
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
 * Class CommunityTopicMassActionParamsType.
 */
class CommunityTopicMassActionParamsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CommunityTopicStatusCategory
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
        $this->defaultStatusCategory = $this->em->getRepository(CommunityTopicStatusCategory::class)->findOneBy(
            ['status_type' => CommunityTopic::STATUS_ACTIVE],
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
                'class'         => CommunityChannel::class,
                'property_path' => 'category',
            ])
            ->add('set_status_category', EntityType::class, [
                'class'         => CommunityTopicStatusCategory::class,
                'property_path' => 'status_category',
            ])
            ->add('set_category', EntityType::class, [
                'class'         => CustomDefCommunityTopic::class,
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
                    CommunityTopic::HIDDEN_STATUS_DELETED,
                    CommunityTopic::HIDDEN_STATUS_DRAFT,
                    CommunityTopic::HIDDEN_STATUS_SPAM,
                    CommunityTopic::HIDDEN_STATUS_UNPUBLISHED,
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
            'data_class' => CommunityTopic::class,
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
        if (!$data instanceof CommunityTopic) {
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
        /** @var CustomDefCommunityTopic $categoryChoice */
        $categoryChoice = $form->get('set_category')->getData();
        if ($categoryChoice) {
            $categoryDef = $categoryChoice->getParent();
            if ($categoryDef) {
                /** @var CustomDataCommunityTopic[]|ArrayCollection $customData */
                $customData = $data->getCustomData()->filter(function (CustomDataCommunityTopic $customData) use ($categoryDef) {
                    return $customData->getRootField() === $categoryDef;
                });

                /* @var CustomDataCommunityTopic $item */
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
            if ($data->getStatus() === CommunityTopic::STATUS_HIDDEN) {
                $data
                    ->setHiddenStatus()
                    ->setStatus(CommunityTopic::STATUS_ACTIVE)
                    ->setStatusCategory($this->defaultStatusCategory)
                ;
            }

            $data->setIsReviewed(true);
        }

        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $data
                ->setStatus(CommunityTopic::STATUS_HIDDEN)
                ->setHiddenStatus(CommunityTopic::HIDDEN_STATUS_DELETED)
                ->setIsReviewed(true)
            ;
        }
    }
}
