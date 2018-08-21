<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationPhoneNumber;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationPhoneNumberType.
 */
class OrganizationPhoneNumberType extends AbstractType
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
                'data_class' => OrganizationPhoneNumber::class,
            ])
            ->setRequired('organization')
            ->setAllowedTypes('organization', Organization::class)
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

        if ($data instanceof OrganizationPhoneNumber && $data->getNumber()) {
            $region   = null;
            $typeCode = null;

            try {
                $region   = PhoneNumbers::getRegionForNumber($data->getNumber());
                $typeCode = PhoneNumbers::getTypeCode($data->getNumber());
            } catch (\Exception $e) {
            }

            $data->setRegion($region);
            $data->setGuessedType($typeCode);
            $data->setOrganization($form->getConfig()->getOption('organization'));
        }
    }
}
