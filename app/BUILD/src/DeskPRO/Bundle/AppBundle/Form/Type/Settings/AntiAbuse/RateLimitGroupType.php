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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RateLimitGroupType.
 */
class RateLimitGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class)
            ->add('limit', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(0),
                ],
            ])
            ->add('time', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(0),
                ],
            ])

            ->add('response', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    RateLimitGroup::RESPONSE_CAPTCHA,
                    RateLimitGroup::RESPONSE_LOCKOUT,
                ],
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'lockoutTimeSet']);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['time'])) {
            $data['time'] *= 60;
            $event->setData($data);
        }
    }

    public function lockoutTimeSet(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['response']) && $data['response'] === RateLimitGroup::RESPONSE_LOCKOUT) {
            $event->getForm()->add('lockout_time', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(0),
                ],
            ]);
            if (isset($data['lockout_time'])) {
                $data['lockout_time'] *= 60;
                $event->setData($data);
            }
        } elseif (isset($data['lockout_time'])) {
            unset($data['lockout_time']);
            $event->setData($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RateLimitGroup::class,
        ]);
    }
}
