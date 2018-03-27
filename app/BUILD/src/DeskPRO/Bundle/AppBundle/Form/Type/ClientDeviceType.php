<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientDeviceType.
 */
class ClientDeviceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['device_id'])) {
            $builder->add('device_id', TextType::class, [
                'required' => true,
            ]);
        }

        $builder
            ->add('device_type', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    ClientDevice::TYPE_GENERIC,
                    ClientDevice::TYPE_IOS_GENERIC,
                    ClientDevice::TYPE_IOS_IPAD,
                    ClientDevice::TYPE_IOS_IPHONE,
                    ClientDevice::TYPE_ANDROID,
                    ClientDevice::TYPE_ANDROID_PHONE,
                    ClientDevice::TYPE_ANDROID_TABLET,
                ],
            ])
            ->add('device_agent', TextType::class, [
                'required' => false,
            ])
            ->add('device_name', TextType::class, [
                'required' => false,
            ])
            ->add('notification_token', TextType::class, [
                'required' => false,
                'mapped'   => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            /* @var ClientDevice $data */
            $cd = $event->getData();

            if ($cd && $cd->canNotify()) {
                $form->get('notification_token')->setData($cd->getNotifyToken());
            } else {
                $form->get('notification_token')->setData('');
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $formData = $event->getData();
            $notifyToken = !empty($formData['notification_token']) ? $formData['notification_token'] : null;

            /* @var ClientDevice $data */
            $cd = $event->getForm()->getData();

            $deviceId = $form->getConfig()->getOption('device_id');
            if (!empty($deviceId)) {
                $cd->setDeviceId($deviceId);
            }

            $cd->setPerson($form->getConfig()->getOption('person'));
            $cd->setAppType($form->getConfig()->getOption('app_type'));

            if ($notifyToken) {
                $cd->enableNotifications($notifyToken);
            } else {
                $cd->disableNotifications();
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'  => ClientDevice::class,
                'device_id'   => '',
                'constraints' => [
                    new UniqueEntity([
                        'fields' => ['device_id', 'person', 'app_type'],
                    ]),
                ],
            ])
            ->setRequired([
                'app_type',
                'person',
            ])
            ->setAllowedTypes('app_type', 'string')
            ->setAllowedTypes('device_id', 'string')
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
