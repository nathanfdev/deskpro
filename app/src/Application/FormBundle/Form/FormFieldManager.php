<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Doctrine\ORM\EntityManager;

/**
 * A service responsible for making sense of "Fields". Usually, special strings (see FormFields class), need to be
 * expanded into more information or fetched from the database.
 */
class FormFieldManager 
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCustomTicketField(CustomDefTicket $field, TicketFormContext $form_context = null)
    {
        return $this->createCustomField($field, $form_context);
    }

    public function getCustomPersonField(CustomDefPerson $field)
    {
        return $this->createCustomField($field);
    }

    public function getCustomTicketFieldById($id, TicketFormContext $form_context = null)
    {
        return $this->em->getRepository('DeskPRO:CustomDefTicket')->find($id);
    }

    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefPerson')->find($id);
    }

    /**
     * @param CustomDefPerson $field
     * @return array
     */
    protected function createCustomField(CustomDefAbstract $field, TicketFormContext $ticket_form_context = null)
    {
        list($type, $value_name, $options) = $this->getFormType($field, $ticket_form_context);
        if (!array_key_exists('label', $options)) {
            $options['label'] = $field->getTitle();
        }
        $options['help'] = $field->getDescription();

        return array($value_name, $type, $options);
    }

    private function getFormType(CustomDefAbstract $field_type, TicketFormContext $ticket_form_context = null)
    {
        switch ($field_type->getHandlerClass()) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
                return array('text', 'input', array(

                ));
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
                return array('textarea', 'input', array(

                ));
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle':
                return array('checkbox', 'value', array(
                    'checkbox_label' => $field_type->getOption('label_text'),
                    'force_boolean' => true
                ));
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Display':
                return array('deskpro_display_html', 'input', array(
                    'html' => $field_type->getOption('html')
                ));
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden':

                $options = array(
                    'auto_fill'          => false,
                    'hidden'             => true,
                    'label'              => false,
                    'cookie_param_name'  => $field_type->getOption('cookie_name'),
                    'request_param_name' => $field_type->getOption('param_name')
                );

                if ($ticket_form_context) {

                    // on agent forms, this is not hidden, so change that here:
                    if ($ticket_form_context->getViewContext() === TicketFormContext::VIEW_AGENT) {
                        $options['hidden'] = false;
                        unset($options['label']); // unset this so that the normal process sets it correctly later
                    } elseif ($ticket_form_context->getVisibility() === TicketFormContext::VISIBILITY_NEW) {
                        // it on the user interface and its a new ticket, we need to signal the form type to auto fill
                        // itself (see Application\FormBundle\Form\Type\HiddenType)
                        $options['auto_fill'] = true;
                    }
                }

                return array('deskpro_hidden', 'input', $options);

            default:
                break;
        }

        throw new \InvalidArgumentException('invalid field. cannot find type for handler class: '.$field_type->getHandlerClass());
    }
}
 