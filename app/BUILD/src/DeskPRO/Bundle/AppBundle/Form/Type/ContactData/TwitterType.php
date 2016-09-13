<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TwitterType.
 */
class TwitterType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_TWITTER;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'property_path' => 'field_1',
            ])
            ->add('display_feed', ApiBooleanType::class, [
                'property_path' => 'field_2',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onChangeUsername']);
    }

    /**
     * @param FormEvent $event
     */
    public function onChangeUsername(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!isset($data['username'])) {
            return;
        }

        /** @var ContactDataAbstract $contact_data */
        $contact_data = $form->getData();

        if ($contact_data->getField1() !== $data['username']) {
            // changing the name - not verified
            $contact_data->setField3('');
            $contact_data->setField10('');
        }
    }
}
