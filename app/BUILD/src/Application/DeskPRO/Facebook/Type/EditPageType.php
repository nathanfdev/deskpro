<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Facebook\Type;

use Application\DeskPRO\Facebook\EditPage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('app', new EditAppType());
        $builder->add('graph_id', 'text', ['required' => true]);
        $builder->add('user_graph_id', 'text', ['required' => true]);
        $builder->add('page_token', 'text', ['required' => true]);
        $builder->add('user_token', 'text', ['required' => true]);
        $builder->add('name', 'text', ['required' => true]);
        $builder->add('picture_url', 'text', ['required' => true]);
        $builder->add('import_wall_posts', 'checkbox', ['required' => false]);
        $builder->add('disable_own_wall_posts', 'checkbox', ['required' => false]);
        $builder->add('import_direct_messages', 'checkbox', ['required' => false]);
        $builder->add('is_enabled', 'hidden', ['required' => false]);
        $builder->add('is_connected', 'hidden', ['required' => false]);
        $builder->add('is_tested', 'hidden', ['required' => false]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => EditPage::class,
                'cascade_validation' => true,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'page';
    }
}
