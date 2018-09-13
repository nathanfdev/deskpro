<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendantDialNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAutoAttendantDialNumberCollectionType.
 */
class VoiceAutoAttendantDialNumberCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (range(1, 9) as $dialNum) {
            $builder->add($dialNum, VoiceTargetType::class);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'mapped'         => false,
                'error_bubbling' => false,
                'constraints'    => [
                    new Assert\Valid(),
                ],
            ])
            ->setRequired('auto_attendant')
            ->setAllowedTypes('auto_attendant', VoiceAutoAttendant::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var VoiceAutoAttendant $autoAttendant */
        $autoAttendant = $form->getConfig()->getOption('auto_attendant');

        foreach ($form->all() as $dialNum => $child) {
            $dialNumber = $autoAttendant->getDialNumber($dialNum);
            $target     = $child->getData();

            if ($target instanceof AbstractVoiceTarget) {
                if (!$dialNumber) {
                    $dialNumber = new VoiceAutoAttendantDialNumber();
                    $dialNumber->setDialNum($dialNum);

                    $autoAttendant->addDialNumber($dialNumber);
                }

                $dialNumber->setTarget($target);
            } elseif ($dialNumber) {
                $autoAttendant->removeDialNumber($dialNumber);
            }
        }
    }
}
