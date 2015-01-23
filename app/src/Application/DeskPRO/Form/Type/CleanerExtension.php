<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Form\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Orb\Input\Cleaner\Cleaner;

class CleanerExtension extends AbstractTypeExtension
{
    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    public function __construct(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'), 128); // high priority
    }

    public function onPreSubmit(FormEvent $event)
    {
        // only clean root form, otherwise all children get cleaned too
        if ($event->getForm()->isRoot()) {
            $raw_data = $event->getData();
            $cleaned_data = $this->cleanData($raw_data, $event->getForm());
            $event->setData($cleaned_data);
        }
    }

    protected function cleanData($raw_data, FormInterface $form)
    {
        $clean_data = array();

        foreach ($raw_data as $form_name => $data) {
            // some fields, like _token, don't actually exist on form, but used by validator.
            // this data is not to worry about, as it isn't mapped into the model data.
            if ($form->has($form_name)) {
                $child_form = $form->get($form_name);

                // to avoid cleaning, form types must explicitly set "filter_clean" to false
                // "filter_clean" will disable cleaning for all of its children, so make sure
                // the option is only set to false if it represents an isolated group of data.
                if ($child_form->getConfig()->getOption('filter_clean', true)) {
                    $form_type = $child_form->getConfig()->getType()->getName();
                    if ('password' === $form_type) {
                        $clean_data[$form_name] = $data;
                        continue; // ignore password type fields
                    }

                    if (is_array($data)) {
                        $cleaned = $this->cleanData($data, $child_form);
                    } else {
                        $cleaned = $this->cleaner->clean($data, 'string');
                    }

                    $clean_data[$form_name] = $cleaned;
                } else {
                    // cleaning was set to false
                    $clean_data[$form_name] = $data;
                }
            } else {
                // form doesnt have this data directly
                $clean_data[$form_name] = $data;
            }
        }

        return $clean_data;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'filter_clean' => true
            )
        )->setAllowedTypes(
            array(
                'filter_clean' => 'bool'
            )
        );
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
