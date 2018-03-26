<?php

namespace Application\DeskPRO\FeedbackTypes\Form\Type;

use Application\DeskPRO\Entity\FeedbackCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackTypePropsType.
 */
class FeedbackTypePropsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', ['required' => true])
            ->add('usergroups', 'entity', [
                'class'         => 'DeskPRO:Usergroup',
                'required'      => false,
                'expanded'      => true,
                'multiple'      => true,
                'property'      => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where(
                        'u.is_agent_group = false AND u.is_enabled = true'
                    );
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeedbackCategory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'feedback_type';
    }
}
