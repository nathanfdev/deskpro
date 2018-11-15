<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
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

        // create entity instance by type
        $target     = null;
        $targetType = isset($data['type']) ? $data['type'] : null;
        if ($targetType) {
            $target = AbstractVoiceTarget::createInstanceByType($targetType);
        }

        // force data submission
        if ($targetType) {
            if (!isset($data['target'])) {
                $data['target'] = null;
            }
        }

        $event->setData($data);
        $form->setData($target);

        // set form fields
        switch ($targetType) {
            case AbstractVoiceTarget::TYPE_QUEUE:
                $form->add('target', EntityType::class, [
                    'property_path' => 'queue',
                    'class'         => VoiceQueue::class,
                ]);

                break;
            case AbstractVoiceTarget::TYPE_AGENT:
                $form->add('target', EntityType::class, [
                    'property_path' => 'agent',
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
                $form->add('target', EntityType::class, [
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
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof AbstractVoiceTarget) {
            $event->setData(null);
        }
    }
}
