<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Attachments;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ApiAttachmentType.
 */
class ApiAttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_inline', ApiBooleanType::class, [
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseAttachmentType::class;
    }
}
