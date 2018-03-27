<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketLayoutFactory.
 */
class TicketLayoutFactory extends AbstractDataService
{
    /**
     * @var CaptchaDecider
     */
    private $captchaDecider;

    /**
     * Constructor.
     *
     * @param EntityManager  $em
     * @param CaptchaDecider $captchaDecider
     */
    public function __construct(EntityManager $em, CaptchaDecider $captchaDecider = null)
    {
        parent::__construct($em);
        $this->captchaDecider = $captchaDecider;
    }

    /**
     * @param mixed $department
     *
     * @return TicketLayout|null
     */
    public function getLayoutForView($department = null)
    {
        return $this->getLayout($department);
    }

    /**
     * Used in the TicketType form type to detect the layout it should use, given the selected dept (or null for initial layout).
     *
     * @param Department|int $department the department entity or its ID
     * @param bool           $forApi
     *
     * @return TicketLayout
     */
    public function getLayoutForTicketForm($department = null, $forApi = false)
    {
        if (!$department instanceof Department) {
            $department = (int) $department ?: null;
        }

        return $this->generateAndCache(
            [
                'getLayoutForTicketForm',
                $department,
                $forApi,
            ],
            function () use ($department, $forApi) {
                $layout = $this->getLayout($department);

                // verify that the user layout has a subject, message, and user email
                $this->verifyRequiredFields($layout->getUserLayout(), $forApi);
                $this->verifyRequiredFields($layout->getAgentLayout(), $forApi);

                if (!$forApi) {
                    $this->checkAntiAbuseCaptcha($layout->getUserLayout());
                }

                // sort the layout fields
                $this->sortTicketLayoutFormFields($layout);

                return $layout;
            }
        );
    }

    /**
     * Gets a combination of all ticket layouts. This is used to output a 'full' form with every field,
     * which is used by JS to dynamically update the UI as a user changes options.
     *
     * @param bool $forApi
     *
     * @return TicketLayout
     */
    public function getFullLayoutForTicketForm($forApi = false)
    {
        $layout = new TicketLayout();

        /** @var TicketLayout[] $all_layouts */
        $all_layouts = $this->getBaseTicketLayoutQueryBuilder()->getQuery()->execute();

        foreach ($all_layouts as $l) {
            foreach ($l->getUserLayout()->all() as $f) {
                $layout->getUserLayout()->add($f);
            }
            foreach ($l->getAgentLayout()->all() as $f) {
                $layout->getAgentLayout()->add($f);
            }
        }

        $this->verifyRequiredFields($layout->getUserLayout(), $forApi);
        $this->verifyRequiredFields($layout->getAgentLayout(), $forApi);

        if (!$forApi) {
            $this->checkAntiAbuseCaptcha($layout->getUserLayout());
        }

        // sort the layout fields
        $this->sortTicketLayoutFormFields($layout);

        return $layout;
    }

    /**
     * @param Layout $layout
     * @param bool   $forApi
     */
    public function verifyRequiredFields(Layout $layout, $forApi = false)
    {
        $requiredFields = [
            FormFields::DEPARTMENT  => 0,
            FormFields::SUBJECT     => 0,
            FormFields::MESSAGE     => 0,
            FormFields::ATTACHMENTS => 0,
        ];

        /** @var \Application\DeskPRO\TicketLayout\LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            if (array_key_exists($layout_field->getFieldType(), $requiredFields)) {
                ++$requiredFields[$layout_field->getFieldType()];
            }
        }

        // if any are still 0, add them to the layout
        foreach ($requiredFields as $field_type => $count) {
            if (0 === $count) {
                $new = new LayoutField($field_type);
                $new->enableOnNew();
                $new->enableOnEdit();
                $new->enableOnView();
                $layout->add($new);
            }
        }

        // if no email input exists, add the new PERSON
        if (!$layout->has(FormFields::PERSON)) {
            $new = new LayoutField(FormFields::PERSON);
            $new->enableOnNew();
            $new->enableOnEdit();
            $new->enableOnView();
            $layout->add($new);
        }

        if ($forApi) {
            if (!$layout->has(FormFields::LABELS)) {
                $new = new LayoutField(FormFields::LABELS);
                $new->enableOnNew();
                $new->enableOnEdit();
                $new->enableOnView();
                $layout->add($new);
            }
        }
    }

    /**
     * This is necessary for Application\DeskPRO\TicketLayout\TicketLayoutManager.
     *
     * @param LayoutCollection $layouts
     */
    public function prepareUserMultipleLayouts(LayoutCollection $layouts)
    {
        foreach ($layouts as $layout) {
            $this->checkAntiAbuseCaptcha($layout);
            $this->sortUserLayoutCollection($layout);
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
        if (!$this->captchaDecider) {
            // only in the PortalKernel will this service be set, ignore it all others.
            return;
        }

        if ($this->captchaDecider->shouldRequireTicketCaptchaForCurrentPerson()) {
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseTicketLayoutQueryBuilder()
    {
        return $this
            ->em
            ->createQueryBuilder()
            ->select('l')
            ->from(TicketLayout::class, 'l')
        ;
    }

    /**
     * @return TicketLayout
     */
    private function getInitialLayout()
    {
        return $this
            ->getBaseTicketLayoutQueryBuilder()
            ->where('l.department IS NULL')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param mixed $department
     *
     * @return TicketLayout|null
     */
    private function getLayout($department = null)
    {
        $layout = null;
        // Note: I removed the following condition, we still definitely want to do this is $dep is a Dep entity.
        //if ($department && !($department instanceof Department)) {
        if ($department) {
            $layout = $this
                ->getBaseTicketLayoutQueryBuilder()
                ->where('l.department = :department')
                ->setParameter('department', $department)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        if (!$layout) {
            $layout = $this->getInitialLayout();
        }

        if ($layout) {
            $layout = clone $layout;
        } else {
            $layout = new TicketLayout();
        }

        return $layout;
    }

    /**
     * @param TicketLayout $layout
     */
    private function sortTicketLayoutFormFields(TicketLayout $layout)
    {
        $this->sortUserLayoutCollection($layout->getUserLayout());
    }

    /**
     * @param Layout $layout
     */
    private function sortUserLayoutCollection(Layout $layout)
    {
        $layout->moveField(FormFields::ATTACHMENTS, FormFields::MESSAGE);
    }
}
