<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Form\Type;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiKeyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('note', 'text', ['required' => true]);
        $builder->add(
            'person',
            'entity',
            [
                'class'         => 'DeskPRO:Person',
                'required'      => false,
                'multiple'      => false,
                'property'      => 'display_name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where(
                        'p.is_agent = true AND p.is_deleted = false'
                    );
                },
            ]
        );

        $builder->add('flags', 'choice', [
            'choices' => [
                ApiKey::FLAG_SUPER_KEY    => ApiKey::FLAG_SUPER_KEY,
                ApiKey::FLAG_ADMIN_MANAGE => ApiKey::FLAG_ADMIN_MANAGE,
                ApiKey::FLAG_API_V1       => ApiKey::FLAG_API_V1,
                ApiKey::FLAG_API_V2       => ApiKey::FLAG_API_V2,
            ],
            'multiple' => true, // an array
            'required' => false,
        ]);

        // cleanup extra data
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $event->setData(array_intersect_key($data, $form->all()));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ApiKey::class,
            ]
        );
    }

    public function getName()
    {
        return 'api_key';
    }
}
