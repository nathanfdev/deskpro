<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\NewsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NewsSettingsType.
 */
class NewsSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AppSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsSettings::class,
        ]);
    }
}
