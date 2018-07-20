<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Logs;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Logs\OptionsModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApiLogsOptionsType.
 */
class ApiLogsOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('modes', ChoiceType::class, [
                'required'          => false,
                'multiple'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    'key',
                    'token',
                    'session',
                ],
            ])
            ->add('request_length', IntegerType::class, [
                'required'      => false,
                'property_path' => 'requestLength',
            ])
            ->add('response_length', IntegerType::class, [
                'required'      => false,
                'property_path' => 'responseLength',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => OptionsModel::class,
            ])
        ;
    }
}
