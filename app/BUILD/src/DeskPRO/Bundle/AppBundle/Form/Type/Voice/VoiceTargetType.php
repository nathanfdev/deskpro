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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceTargetType.
 */
class VoiceTargetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'mapped'            => false,
                'choices_as_values' => true,
                'choices'           => [
                    AbstractVoiceTarget::TYPE_QUEUE,
                    AbstractVoiceTarget::TYPE_AGENT,
                    AbstractVoiceTarget::TYPE_AUTO_ATTENDANT,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddTargetField'], 100);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onEnsureTargetFieldSubmission'], 100);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCreateEntityInstance'], 200);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onUnsetTarget']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AbstractVoiceTarget::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onAddTargetField(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $targetType = isset($data['type']) ? $data['type'] : null;

        switch ($targetType) {
            case AbstractVoiceTarget::TYPE_QUEUE:
                $form->add('queue', EntityType::class, [
                    'class' => VoiceQueue::class,
                ]);

                break;
            case AbstractVoiceTarget::TYPE_AGENT:
                $form->add('agent', EntityType::class, [
                    'class'         => Person::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->where('u.is_agent = 1')
                        ;
                    },
                ]);

                break;
            case AbstractVoiceTarget::TYPE_AUTO_ATTENDANT:
                $form->add('auto_attendant', EntityType::class, [
                    'class'         => VoiceAutoAttendant::class,
                    'property_path' => 'autoAttendant',
                ]);

                break;
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onCreateEntityInstance(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $target = null;
        if (isset($data['type'])) {
            $target = AbstractVoiceTarget::createInstanceByType($data['type']);
        }

        $form->setData($target);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onUnsetTarget(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof AbstractVoiceTarget) {
            $event->setData(null);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onEnsureTargetFieldSubmission(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['type'])) {
            switch ($data['type']) {
                case AbstractVoiceTarget::TYPE_QUEUE:
                    if (!isset($data['queue'])) {
                        $data['queue'] = null;
                    }

                    break;
                case AbstractVoiceTarget::TYPE_AGENT:
                    if (!isset($data['agent'])) {
                        $data['agent'] = null;
                    }

                    break;
                case AbstractVoiceTarget::TYPE_AUTO_ATTENDANT:
                    if (!isset($data['auto_attendant'])) {
                        $data['auto_attendant'] = null;
                    }

                    break;
            }
        }

        $event->setData($data);
    }
}
