<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class VoiceBuyNumberType.
 */
class VoiceBuyNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('number', TextType::class, [
            'property_path' => '[phoneNumber]',
        ]);
    }
}
