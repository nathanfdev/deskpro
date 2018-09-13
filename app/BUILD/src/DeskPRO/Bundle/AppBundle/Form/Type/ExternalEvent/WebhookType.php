<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WebhookType.
 */
class WebhookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, ['required' => true])
            ->add('type', ChoiceType::class, [
                    'required'          => true,
                    'choices_as_values' => true,
                    'choices'           => [
                        'POST',
                        'GET',
                        'DELETE',
                        'PUT',
                    ],
            ])
            ->add('title', TextType::class, ['required' => true])
            ->add('data', JsonArrayType::class, ['required' => true])
        ;
    }
}
