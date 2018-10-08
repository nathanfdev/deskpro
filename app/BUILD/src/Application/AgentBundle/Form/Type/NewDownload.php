<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewDownload extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // Basic fields
        //------------------------------

        $builder->add('title', 'text', ['required' => false]);
        $builder->add('content', 'textarea', ['required' => false, 'filter_clean' => false]);
        $builder->add('status', 'text');

        $builder->add('fileurl', 'text', ['required' => false]);
        $builder->add('filesize', 'text', ['required' => false]);
        $builder->add('filename', 'text', ['required' => false]);

        $builder->add('category_id', 'text');
        $builder->add('slug', 'text');

        $builder->add('labels', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('blob_inline_ids', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('attach', 'hidden');
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => \Application\AgentBundle\Form\Model\NewDownload::class,
        ];
    }

    public function getName()
    {
        return 'newdownload';
    }
}
