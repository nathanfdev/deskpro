<?php

namespace DeskPRO\Bundle\ApiBundle\Form\Type\Batch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PostBatchRequestType.
 */
class PostBatchRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('requests', BatchRequestsType::class, [
            'required' => true,
        ]);
    }
}
