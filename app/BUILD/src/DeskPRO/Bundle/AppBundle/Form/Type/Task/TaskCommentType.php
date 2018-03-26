<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TaskCommentType.
 */
class TaskCommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', HtmlTextareaType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\NotBlank(),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelatedData'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TaskComment::class,
            ])
            ->setRequired(['task', 'person'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('task', Task::class)
        ;
    }

    /**
     * Set related data for new comment.
     *
     * @param FormEvent $event
     */
    public function onSetRelatedData(FormEvent $event)
    {
        $data   = $event->getData();
        $config = $event->getForm()->getConfig();
        if ($data instanceof TaskComment && !$data->getId()) {
            $data->setTask($config->getOption('task'));
            $data->setPerson($config->getOption('person'));
        }
    }
}
