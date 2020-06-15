<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\AliasType;
use Application\DeskPRO\CustomFields\Form\StringObject;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldTypeAbstract.
 */
class CustomFieldTypeAbstract extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $field = $builder->getData()->getField();

        //------------------------------
        // Basic fields
        //------------------------------

        $builder->add('title', 'text', ['required' => true]);
        // use own string type instead of symfony's text type to avoid automatic conversion of empty string or null to null
        // this means we get to treat the cases where alias is not set and is set to empty { alias: "" }
        $builder->add('alias', AliasType::class, [
            'required'               => false,
            'data_class'             => StringObject::class,
            'null_handling_strategy' => 'null',
            'constraints'            => [
                new AppAssert\ObjectAlias([
                    'owner' => $field,
                ]),
            ],
        ]);
        $builder->add('description', 'textarea', ['required' => false]);
        $builder->add('default_value', 'text', ['required' => false]);
        $builder->add('handler_class', 'hidden', ['required' => true]);
        $builder->add('validation_type', 'hidden', ['required' => false]);
        $builder->add('agent_validation_type', 'hidden', ['required' => false]);
        $builder->add('agent_validation_resolve', 'hidden', ['required' => false]);

        $builder->add('required', ApiBooleanType::class, ['required' => false]);
        $builder->add('agent_required', ApiBooleanType::class, [
            'required' => false,
        ]);

        $builder->add('custom_css_classname', 'text', ['required' => false]);
        $builder->add('is_enabled', 'checkbox', ['required' => false]);
        $builder->add('is_agent_field', 'checkbox', ['required' => false]);

        // Used only by CustomDefPerson
        $builder->add('is_public', 'checkbox', ['required' => false]);

        // used only by CustomDefCommunityTopic
        if ($field instanceof CustomDefCommunityTopic) {
            $builder->add('is_global', 'checkbox', ['required' => false]);
            $builder->add('forums', CollectionType::class, [
                'entry_type'    => CommunityFieldToForumType::class,
                'entry_options' => [
                    'custom_field' => $field,
                ],
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => true,
            ]);
            $builder->add('brand', EntityType::class, [
                'class' => Brand::class,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'fielddef';
    }
}
