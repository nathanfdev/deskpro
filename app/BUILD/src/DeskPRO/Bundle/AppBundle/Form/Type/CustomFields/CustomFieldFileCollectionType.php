<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Attachments\RestrictionSet;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldFileCollectionType.
 */
class CustomFieldFileCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var CustomDefAbstract $def */
        $def    = $options['custom_field'];
        $prefix = $options['agent_interface'] ? 'agent_' : 'user_';

        $view->vars['multiple']              = $def->getOption('multiple');
        $view->vars['extensions_limit_mode'] = $def->getOption($prefix.'extensions_limit_mode');
        $view->vars['must_extensions']       = $def->getOption($prefix.'must_extensions', []);
        $view->vars['not_extensions']        = $def->getOption($prefix.'not_extensions', []);
        $view->vars['max_file_size']         = $def->getOption($prefix.'max_file_size');
        $view->vars['restriction_set']       = RestrictionSet::getSetIdForCustomField($def);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'   => CustomFieldFileType::class,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
            ->setRequired(['custom_field', 'agent_interface'])
            ->setRequired('custom_field')
            ->setAllowedTypes('agent_interface', 'bool')
            ->setAllowedTypes('custom_field', CustomDefAbstract::class)
        ;
    }
}
