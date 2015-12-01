<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use DeskPRO\Bundle\PortalBundle\Form\FormFields;
use Doctrine\ORM\EntityManager;

class TicketLayoutFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager;

    /**
     * @var CaptchaDecider
     */
    private $catpcha_decider;

    public function __construct(EntityManager $entity_manager, CaptchaDecider $catpcha_decider = null)
    {
        $this->entity_manager  = $entity_manager;
        $this->catpcha_decider = $catpcha_decider;
    }

    /**
     * @return TicketLayout
     */
    public function getInitialLayout()
    {
        return $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department IS NULL')
                                    ->getOneOrNullResult();
    }

    public function getLayout($department = null)
    {
        $layout = null;
        if ($department && !($department instanceof Department)) {
            $layout = $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department = :department')
                ->setParameter('department', $department)
                ->getOneOrNullResult();
        }

        if (!$layout) {
            $layout = $this->getInitialLayout();
        }

        $layout = clone $layout;

        return $layout;
    }

    public function getLayoutForView($department = null)
    {
        return $this->getLayout($department);
    }

    /**
     * Used in the TicketType form type to detect the layout it should use, given the selected dept (or null for initial layout).
     *
     * @param Department|int $department the department entity or its ID
     *
     * @return TicketLayout
     */
    public function getLayoutForTicketForm($department = null)
    {
        // TODO: add a quick cahing layer here so that we only ever calc this once per department in a request
        // TODO: do what we do in the DataService's with the in memory hash map.
        $layout = $this->getLayout($department);

        // verify that the user layout has a subject, message, and user email
        $this->verifyRequiredFields($layout->getUserLayout());
        $this->verifyRequiredFields($layout->getAgentLayout());
        $this->checkAntiAbuseCaptcha($layout->getUserLayout());
        //$this->checkAntiAbuseCaptcha($layout->getAgentLayout()); purposely not checking for agent interface

        return $layout;
    }

    /**
     * Gets a combination of all ticket layouts. This is used to output a 'full' form with every field,
     * which is used by JS to dynamically update the UI as a user changes options.
     *
     * @return TicketLayout
     */
    public function getFullLayoutForTicketForm()
    {
        $layout = new TicketLayout();

        /** @var TicketLayout[] $all_layouts */
        $all_layouts = $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l')->execute();

        foreach ($all_layouts as $l) {
            foreach ($l->getUserLayout()->all() as $f) {
                $layout->getUserLayout()->add($f);
            }
            foreach ($l->getAgentLayout()->all() as $f) {
                $layout->getAgentLayout()->add($f);
            }
        }

        $this->verifyRequiredFields($layout->getUserLayout());
        $this->verifyRequiredFields($layout->getAgentLayout());

        $this->checkAntiAbuseCaptcha($layout->getUserLayout());
        //$this->checkAntiAbuseCaptcha($layout->getAgentLayout()); purposely not checking for agent interface

        return $layout;
    }

    protected function verifyRequiredFields(Layout $layout)
    {
        $required_fields = [
            FormFields::DEPARTMENT => 0,
            FormFields::SUBJECT    => 0,
            FormFields::MESSAGE    => 0,
        ];

        /** @var \Application\DeskPRO\TicketLayout\LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            if (array_key_exists($layout_field->getFieldType(), $required_fields)) {
                ++$required_fields[$layout_field->getFieldType()];
            }
        }

        // if any are still 0, add them to the layout
        foreach ($required_fields as $field_type => $count) {
            if (0 === $count) {
                $new = new LayoutField($field_type);
                $new->enableOnNew();
                $new->enableOnEdit();
                $new->enableOnView();
                $layout->add($new);
            }
        }

        // if no email input exists, add the new USER_NAME_AND_EMAIL
        if (!$layout->has(FormFields::USER_EMAIL) && !$layout->has(FormFields::USER_NAME_AND_EMAIL)) {
            $new = new LayoutField(FormFields::USER_NAME_AND_EMAIL);
            $new->enableOnNew();
            $new->enableOnEdit();
            $new->enableOnView();
            $layout->add($new);
        }

        // finally, if USER_NAME_AND_EMAIL exists, remove USER_EMAIL and USER_NAME as they are redundant
        if ($layout->has(FormFields::USER_NAME_AND_EMAIL)) {
            if ($layout->has(FormFields::USER_EMAIL)) {
                $layout->remove(FormFields::USER_EMAIL);
            }
            if ($layout->has(FormFields::USER_NAME)) {
                $layout->remove(FormFields::USER_NAME);
            }
        }
    }

    /**
     * This is necessary for Application\DeskPRO\TicketLayout\TicketLayoutManager.
     *
     * @param LayoutCollection $layouts
     */
    public function checkAntiAbuseCaptchaForMultipleLayouts(LayoutCollection $layouts)
    {
        foreach ($layouts as $layout) {
            $this->checkAntiAbuseCaptcha($layout);
        }
    }

    /**
     * The admin has the ability to add a CAPTCHA field to the layout on their own.
     * IF a captcha field is present in the layout definition it will always be rendered on the form.
     *
     * However, if captcha is NOT on the layout of the ticket, a CAPTCHA can still be rendered if
     * anti-abuse settings are violated. This method will force-add CAPTCHA to the ticket layout
     * in the event that anti-abuse is in effect.
     *
     * @param Layout $layout
     */
    private function checkAntiAbuseCaptcha(Layout $layout)
    {
        if (!$this->catpcha_decider) {
            // only in the PortalKernel will this service be set, ignore it all others.
            return;
        }

        if ($this->catpcha_decider->shouldRequireTicketCaptchaForCurrentPerson()) {
            $exists_in_layout = false;
            /** @var \Application\DeskPRO\TicketLayout\LayoutField $layout_field */
            foreach ($layout as $layout_field) {
                if ($layout_field->getFieldType() == FormFields::CAPTCHA) {
                    $exists_in_layout = true;
                }
            }

            if (!$exists_in_layout) {
                $new = new LayoutField(FormFields::CAPTCHA, 'captcha_auto_added');
                $new->enableOnNew();
                $new->enableOnEdit();
                $new->enableOnView();
                $layout->add($new);
            }
        }
    }
}
