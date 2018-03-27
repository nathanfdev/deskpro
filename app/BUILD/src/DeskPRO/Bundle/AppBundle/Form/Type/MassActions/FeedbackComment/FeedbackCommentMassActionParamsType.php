<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\FeedbackComment;

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionParamsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackCommentMassActionParamsType.
 */
class FeedbackCommentMassActionParamsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionParamsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'actions'    => ['approve', 'delete'],
            'data_class' => FeedbackComment::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof FeedbackComment) {
            return;
        }

        if (BaseMassActionParamsType::hasAction($event, 'approve')) {
            $data->setStatus(FeedbackComment::STATUS_VISIBLE);
        }

        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $data->setStatus(FeedbackComment::STATUS_DELETED);
        }
    }
}
