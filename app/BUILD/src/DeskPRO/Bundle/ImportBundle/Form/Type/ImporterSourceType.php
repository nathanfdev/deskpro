<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type;

use DeskPRO\Bundle\ImportBundle\Form\Type\Source\AdvancedSourceType;
use DeskPRO\Bundle\ImportBundle\Form\Type\Source\KayakoSourceType;
use DeskPRO\Bundle\ImportBundle\Form\Type\Source\OsTicketSourceType;
use DeskPRO\Bundle\ImportBundle\Form\Type\Source\ZendeskSourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ImporterSourceType.
 */
class ImporterSourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices_as_values' => true,
            'choices'           => [
                'zendesk',
                'kayako',
                'osticket',
                'advanced',
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // set source options field by importer type
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'zendesk':
                    $form->add('options', ZendeskSourceType::class);
                    break;
                case 'kayako':
                    $form->add('options', KayakoSourceType::class);
                    break;
                case 'osticket':
                    $form->add('options', OsTicketSourceType::class);
                    break;
                case 'advanced':
                    $form->add('options', AdvancedSourceType::class);
                    break;
            }
        }

        // force send options
        if ($form->has('options') && !isset($data['options'])) {
            $data['options'] = [];
        }

        $event->setData($data);
    }
}
