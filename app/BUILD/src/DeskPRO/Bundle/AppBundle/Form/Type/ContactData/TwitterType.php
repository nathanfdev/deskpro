<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TwitterType.
 */
class TwitterType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_TWITTER;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'property_path' => 'field_1',
            ])
            ->add('display_feed', ApiBooleanType::class, [
                'property_path' => 'field_2',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onChangeUsername']);
    }

    /**
     * @param FormEvent $event
     */
    public function onChangeUsername(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!isset($data['username'])) {
            return;
        }

        /** @var ContactDataAbstract $contact_data */
        $contact_data = $form->getData();

        if ($contact_data->getField1() !== $data['username']) {
            // changing the name - not verified
            $contact_data->setField3('');
            $contact_data->setField10('');
        }
    }
}
