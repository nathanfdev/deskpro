<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

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
                'filter_clean' => true,
            ])
            ->setAllowedTypes('filter_clean', 'bool')
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
                        $cleaned = $this->cleanNonCompoundData($data);
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
     * @param mixed $rawData
     *
     * @return mixed
     */
    public function cleanNonCompoundData($rawData)
    {
        if (is_string($rawData)) {
            $cleaned = $this->cleaner->clean($rawData, 'string');
        } else {
            $cleaned = $rawData;
        }

        return $cleaned;
    }
}
