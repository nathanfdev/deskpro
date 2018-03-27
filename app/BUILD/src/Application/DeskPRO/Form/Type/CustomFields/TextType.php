<?php

namespace Application\DeskPRO\Form\Type\CustomFields;

use Symfony\Component\Form\FormBuilderInterface;

class TextType extends CustomFieldType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('input', 'text', $this->getValueOptions());
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'cf_text';
    }
}
