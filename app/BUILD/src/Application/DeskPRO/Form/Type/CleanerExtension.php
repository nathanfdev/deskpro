<?php

namespace Application\DeskPRO\Form\Type;

use Orb\Input\Cleaner\Cleaner;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CleanerExtension.
 */
class CleanerExtension extends AbstractTypeExtension
{
    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    /**
     * Constructor.
     *
     * @param Cleaner $cleaner
     */
    public function __construct(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'filter_clean'      => true,
                'filter_clean_type' => 'string',
            ])
            ->setAllowedTypes('filter_clean', 'bool')
            ->setAllowedTypes('filter_clean_type', 'string')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // only clean root form, otherwise all children get cleaned too
        $form = $event->getForm();

        if ($form->isRoot()) {
            $rawData = $event->getData();

            if ($form->getConfig()->getOption('compound')) {
                $cleanedData = $this->cleanCompoundData($rawData, $form);
            } else {
                $cleanedData = $this->cleanNonCompoundData($rawData);
            }

            $event->setData($cleanedData);
        }
    }

    /**
     * @param array         $rawData
     * @param FormInterface $form
     *
     * @return array
     */
    protected function cleanCompoundData($rawData, FormInterface $form)
    {
        $cleanData = [];

        if (empty($rawData)) {
            return [];
        }

        foreach ($rawData as $formName => $data) {
            // some fields, like _token, don't actually exist on form, but used by validator.
            // this data is not to worry about, as it isn't mapped into the model data.
            if ($form->has($formName)) {
                $childForm = $form->get($formName);

                // to avoid cleaning, form types must explicitly set "filter_clean" to false
                // "filter_clean" will disable cleaning for all of its children, so make sure
                // the option is only set to false if it represents an isolated group of data.
                if ($childForm->getConfig()->getOption('filter_clean', true)) {
                    if (is_array($data)) {
                        $cleaned = $this->cleanCompoundData($data, $childForm);
                    } else {
                        $cleanType = $childForm->getConfig()->getOption('filter_clean_type', 'string');
                        $cleaned   = $this->cleanNonCompoundData($data, $cleanType);
                    }

                    $cleanData[$formName] = $cleaned;
                } else {
                    // cleaning was set to false
                    $cleanData[$formName] = $data;
                }
            } else {
                // form doesnt have this data directly
                $cleanData[$formName] = $data;
            }
        }

        return $cleanData;
    }

    /**
     * @param mixed  $rawData
     * @param string $cleanType
     *
     * @return mixed
     */
    public function cleanNonCompoundData($rawData, $cleanType)
    {
        if (is_string($rawData)) {
            $cleaned = $this->cleaner->clean($rawData, $cleanType);
        } else {
            $cleaned = $rawData;
        }

        return $cleaned;
    }
}
