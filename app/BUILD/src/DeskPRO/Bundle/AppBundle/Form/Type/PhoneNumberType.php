<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhoneNumberType.
 */
class PhoneNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'phone_number';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', TextType::class)
            ->add('extension', TextType::class, [
                'required'      => false,
                'property_path' => 'ext',
            ])
            ->add('label', TextType::class, [
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PhoneNumber::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
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
        $data = $form->getData();

        /*
         * moved from PhoneNumber entity:
         * We do logic here (with the help of Google's libphonenumber) to
         * get the region code, and validate/format the number.
         */

        if ($data instanceof PhoneNumber && $data->getNumber()) {
            $data->setRegion(PhoneNumbers::getRegionForNumber($data->getNumber()));
            $data->setGuessedType(PhoneNumbers::getTypeCode($data->getNumber()));
            $data->setPerson($form->getConfig()->getOption('person'));
        }
    }
}
