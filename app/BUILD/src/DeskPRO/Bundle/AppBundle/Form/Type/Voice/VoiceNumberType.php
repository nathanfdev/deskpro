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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceNumberType.
 */
class VoiceNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', EntityType::class, [
                'class'    => VoiceAccount::class,
                'required' => true,
            ])
            ->add('sid', TextType::class, [
                'required' => true,
            ])
            ->add('nickname', TextType::class, [
                'required' => false,
            ])
            ->add('number', TextType::class, [
                'required' => true,
            ])
            ->add('country_code', TextType::class, [
                'property_path' => 'countryCode',
                'required'      => true,
            ])
            ->add('target', VoiceTargetType::class, [
                'error_bubbling' => false,
                'required' => true,
            ])
            ->add('outbound_calls_enabled', ApiBooleanType::class, [
                'property_path' => 'outboundCallsEnabled',
                'required'      => false,
            ])
        ;

        $builder->get('country_code')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onLowerCountryCode']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoiceNumber::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onLowerCountryCode(FormEvent $event)
    {
        $data = $event->getData();
        if (is_string($data)) {
            $event->setData(strtolower($data));
        }
    }
}
