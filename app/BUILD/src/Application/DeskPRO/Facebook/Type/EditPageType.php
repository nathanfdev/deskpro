<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
