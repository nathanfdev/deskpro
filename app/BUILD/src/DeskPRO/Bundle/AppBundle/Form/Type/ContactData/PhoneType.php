<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhoneType.
 */
class PhoneType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_PHONE;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'property_path' => 'field_3',
                'choices'       => [
                    'phone'  => 'Phone',
                    'mobile' => 'Mobile',
                    'fax'    => 'Fax',
                ],
            ])
            ->add('code', TextType::class, [
                'property_path' => 'field_1',
            ])
            ->add('number', TextType::class, [
                'property_path' => 'field_2',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'error_mapping' => [
                'field_9' => 'number',
            ],
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        // prepare phone code
        /** @var ContactDataAbstract $data */
        $data = $event->getData();
        $data->setField1(RegexUtils::safePregReplace('#[^0-9]#', '', $data->getField1()));

        // set searchable field
        $number = '+'.$data->getField1().' '.$data->getField2();

        $data->setField9($number);
        $data->setField10(RegexUtils::safePregReplace('#[^0-9a-zA-Z]#', '', $number));
    }
}
