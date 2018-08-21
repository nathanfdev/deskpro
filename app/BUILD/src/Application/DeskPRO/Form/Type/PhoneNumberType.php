<?php

namespace Application\DeskPRO\Form\Type;

use Application\DeskPRO\Entity\PersonPhoneNumber;
use Application\DeskPRO\Form\Transformer\PhoneNumberModelTransformer;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhoneNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // NOTE: this is a "hidden" field. You should instantiate "DeskPRO.UI.PhoneNumberInputs" on the client
        // and call "renderPhoneInputs()" after the form is drawn. It is safe to call that method any number of
        // times if you are using this in a collection type.

        $builder->add('number', 'hidden', [
            'required' => false,
            'label'    => false,
            'attr'     => [
                'class' => 'dp_phone_number_hidden',
            ],
            'constraints' => [
                new NotBlank(['message' => 'Phone number is invalid.']),
            ],
        ]);

        $builder->add('ext', 'hidden', [
            'required' => false,
            'label'    => false,
            'attr'     => [
                'class' => 'dp_phone_ext_hidden',
            ],
        ]);
        $builder->get('number')->addModelTransformer(new PhoneNumberModelTransformer());

        if ($options['show_phone_label']) {
            $builder->add('label', 'text', [
                'label' => false,
                'attr'  => [
                    'class' => 'phone_label',
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getForm()->getData();

            /*
             * moved from PhoneNumber entity:
             * We do logic here (with the help of Google's libphonenumber) to
             * get the region code, and validate/format the number.
             */

            if ($data && $data['number']) {
                $number = $data['number'];
                $data['region'] = PhoneNumbers::getRegionForNumber($number);
                $data['guessed_type'] = PhoneNumbers::getTypeCode($number);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'       => PersonPhoneNumber::class,
            'show_phone_label' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'phone_number';
    }
}
