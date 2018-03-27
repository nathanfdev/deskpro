<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermTypeCodes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TermEngineTermOptionsType.
 */
class TermEngineTermOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['term_type']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $options = $event->getData();
        $form    = $event->getForm();

        if (!is_array($options)) {
            $options = [];
        }

        // what term are we dealing with for this options?
        $term_type = $form->getConfig()->getOption('term_type');

        try {
            $term_class = TermTypeCodes::getTermClassForTypeCode($term_type);
        } catch (TermTypeDoesNotExistException $e) {
            $form->addError(new FormError(ErrorsCodes::TERM_TYPE_DOES_NOT_EXIST, null, ['type' => $term_type]));

            return;
        }

        // what options are defined for this term?
        $options_resolver = call_user_func([$term_class, 'getOptionsResolver']);
        $defined          = $options_resolver->getDefinedOptions();

        // add a form child for each option here
        foreach ($defined as $option_name) {
            // we have the option value, and we need to dynamically figure out
            // what "type" of form to use (text, number, etc)
            // we can guess based on the type
            $type         = 'text'; // default to a text form type
            $form_options = [];
            // NOTE: to enhance our "guessing" algorithm, we could use the $options_resolver above to inspect the
            //       "allowed types" array on the various options and make a decision based on them.

            if (array_key_exists($option_name, $options)) {
                if (is_array($options[$option_name])) {
                    $type         = 'collection';
                    $form_options = [
                        'type'         => 'text',
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'delete_empty' => true,
                    ];
                } elseif (is_string($options[$option_name])) {
                    $type = 'text';
                } elseif (is_numeric($options[$option_name])) {
                    $type = 'number';
                }
            }

            $form_options = array_merge($form_options, ['error_bubbling' => false]); // never bubble errors
            $form->add($option_name, $type, $form_options);
        }
    }
}
