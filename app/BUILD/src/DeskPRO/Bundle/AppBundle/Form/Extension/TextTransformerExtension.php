<?php

namespace DeskPRO\Bundle\AppBundle\Form\Extension;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TextStringTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TextTransformerExtension.
 */
class TextTransformerExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new TextStringTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return TextType::class;
    }
}
