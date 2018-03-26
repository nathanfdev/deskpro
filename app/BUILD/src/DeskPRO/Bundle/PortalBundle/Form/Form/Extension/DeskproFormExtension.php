<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Extension;

use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\ForceBooleanTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeskproFormExtension.
 */
class DeskproFormExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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
            'extra_fields_message' => 'portal.forms.error_extra_fields',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['force_boolean']) {
            $builder->addModelTransformer(new ForceBooleanTransformer());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help']            = $options['help'];
        $view->vars['is_root']         = $form->isRoot();
        $view->vars['has_root_parent'] = $form->getParent() ? $form->getParent()->isRoot() : false;
        $view->vars['fully_hidden']    = $options['fully_hidden'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
