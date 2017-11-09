<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
