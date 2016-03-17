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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Extension;

use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\ForceBooleanTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeskproFormExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'help'                  => '',
                'force_boolean'         => false,
                'fully_hidden'          => false,
                'post_max_size_message' => 'portal.forms.error_server_rejected_size',
                'saved_form_subrequest' => false,
                'allow_extra_fields'    => function (Options $options) {
                    // if its a saved form subrequest, allow extra fields
                    // this is because we disable things like catpcha, and csrf, and they may
                    // be present in the form data even though we've removed them from the actual form
                    return $options['saved_form_subrequest'];
                },
                'extra_fields_message' => 'portal.forms.extra_fields',
            )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['force_boolean']) {
            $builder->addModelTransformer(new ForceBooleanTransformer());
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help']            = $options['help'];
        $view->vars['is_root']         = $form->isRoot();
        $view->vars['has_root_parent'] = $form->getParent() ? $form->getParent()->isRoot() : false;
        $view->vars['fully_hidden']    = $options['fully_hidden'];
    }

    public function getExtendedType()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }
}
