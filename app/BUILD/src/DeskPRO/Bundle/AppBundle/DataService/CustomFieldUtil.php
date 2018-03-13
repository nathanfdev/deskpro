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

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\CustomFields\Handler\Date;
use Application\DeskPRO\CustomFields\Handler\DateTime;
use Application\DeskPRO\CustomFields\Handler\Display;
use Application\DeskPRO\CustomFields\Handler\Hidden;
use Application\DeskPRO\CustomFields\Handler\Text;
use Application\DeskPRO\CustomFields\Handler\Textarea;
use Application\DeskPRO\CustomFields\Handler\Toggle;
use Application\DeskPRO\CustomFields\Handler\Url;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class CustomFieldUtil.
 *
 * todo fix me, does not work properly with choice fields
 */
class CustomFieldUtil
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param CustomDefAbstract  $fieldDef
     * @param CustomDataAbstract $data
     *
     * @return bool|string
     */
    public function getValueForCustomFormField(CustomDefAbstract $fieldDef, CustomDataAbstract $data)
    {
        switch ($fieldDef->getHandlerClass()) {
            case Date::class:
                $value = Date::getDisplayValue($data->getData(), $fieldDef->getOption('calendar'));
                break;
            case DateTime::class:
                try {
                    /** @var \Application\DeskPRO\Entity\Person $user */
                    $token    = $this->tokenStorage->getToken();
                    $user     = $token ? $token->getUser() : null;
                    $timezone = new \DateTimeZone($user instanceof Person ? $user->getTimezone() : 'UTC');

                    if (is_numeric($data->getData())) {
                        $datetime = new \DateTime('@'.$data->getData());
                    } else {
                        $datetime = new \DateTime($data->getData());
                    }

                    $datetime->setTimezone($timezone);

                    $value = $datetime->format('F j, Y, g:i a');
                } catch (\Exception $e) {
                    $value = ''.$data->getData();
                }
                break;
            case Toggle::class:
                if ($data->getData() == 1) {
                    $value = $fieldDef->getOption('label_text') ?: 'Checked';
                } else {
                    $value = 'None';
                }
                break;
            case Text::class:
            case Textarea::class:
                $value = $data->getData();
                break;
            case Choice::class:
                if (!$data->getValue()) {
                    $ids = explode(',', $data->getInput());
                } else {
                    $ids = [$data->getFieldId()];
                }
                $selected = [];
                foreach ($ids as $id) {
                    if ($selectedField = $fieldDef->getChildById($id)) {
                        $selected[] = $selectedField->getTitle();
                    }
                }
                $value = implode(', ', $selected);
                break;
            case Hidden::class:
                $value = $data->getInput();
                break;
            case Display::class:
                $value = $fieldDef->getHtmlOption();
                break;
            case Url::class:
                $value = $data->getInput();
                break;
            default:
                $value = null;
                break;
        }

        return $value;
    }

    /**
     * @param CustomDefAbstract $field
     * @param                   $customData
     *
     * @return CustomDataAbstract[]
     */
    public function getCustomDataForField(CustomDefAbstract $field, $customData)
    {
        $fieldId = $field->getId();
        $matches = [];

        /** @var CustomDataAbstract $data */
        foreach ($customData as $data) {
            $cdField     = $data->getField();
            $cdRootField = $data->getRootField();

            if (($cdField && $cdField->getId() === $fieldId) || ($cdRootField && $cdRootField->getId() === $fieldId)) {
                $matches[] = $data;
            }
        }

        if (empty($matches)) {
            return [];
        }

        return $matches;
    }
}
