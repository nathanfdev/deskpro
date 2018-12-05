<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackStatuses\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackStatusPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', ['required' => true]);
        $builder->add('status_type', 'choice', [
            'choices'  => ['active' => 'active', 'closed' => 'closed'],
            'required' => true,
        ]);
        $builder->add('brand', EntityType::class, [
            'class' => Brand::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeedbackStatusCategory::class,
        ]);
    }

    public function getName()
    {
        return 'feedback_status_category';
    }
}
