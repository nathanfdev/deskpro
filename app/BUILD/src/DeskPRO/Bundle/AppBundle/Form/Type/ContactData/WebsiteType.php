<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WebsiteType.
 */
class WebsiteType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_WEBSITE;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', TextType::class, [
            'property_path' => 'field_1',
        ]);
    }
}
