<?php

namespace Application\DeskPRO\Form\Type;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;

class PersonPhoneNumbersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('phone_numbers', 'collection', [
            'type'         => new PhoneNumberType(),
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'person_phones';
    }
}
