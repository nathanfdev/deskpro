<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\WidgetSetup\BrandSettings;

use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetTicketSetupType.
 */
class WidgetTicketSetupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'widget_ticket_setup';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('select_department', 'choice', [
                'choices' => [
                    'default' => 'Default department',
                    'custom'  => 'User selects department',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('default_department', 'number')
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onDefaultDepartment']);
    }

    /**
     * @param FormEvent $event
     */
    public function onDefaultDepartment(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data['select_department'] === 'default' && !$data['default_department']) {
            $form->get('default_department')->addError(new FormError(ApiErrors::NOT_BLANK));
        }
    }
}
