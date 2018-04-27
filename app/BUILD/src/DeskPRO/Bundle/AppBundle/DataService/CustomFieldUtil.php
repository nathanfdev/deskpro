<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\CustomFields\Handler\Currency;
use Application\DeskPRO\CustomFields\Handler\Date;
use Application\DeskPRO\CustomFields\Handler\DateTime;
use Application\DeskPRO\CustomFields\Handler\Display;
use Application\DeskPRO\CustomFields\Handler\File;
use Application\DeskPRO\CustomFields\Handler\Hidden;
use Application\DeskPRO\CustomFields\Handler\Text;
use Application\DeskPRO\CustomFields\Handler\Textarea;
use Application\DeskPRO\CustomFields\Handler\Toggle;
use Application\DeskPRO\CustomFields\Handler\Url;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Currency as CurrencyEntity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class CustomFieldUtil.
 *
 * todo fix me, does not work properly with choice fields
 */
class CustomFieldUtil
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param TokenStorage  $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em           = $em;
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
                    $value = $fieldDef->getOption('unchecked_text') ?: 'None';
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
            case Currency::class:
                $value      = null;
                $currencyId = $fieldDef->getOption('currency_id');
                if ($currencyId) {
                    $currency = $this->em->getRepository(CurrencyEntity::class)->find($currencyId);
                    if ($currency) {
                        $value = $data->getData() ? ($data->getData() / $currency->getDelimiter()) : 0;
                        $value = $currency->getSymbol().' '.number_format($value, $currency->getDecimalPlaces(), '.', ',');
                    }
                }

                break;
            case File::class:
                $value = null;
                if ($data->getValue()) {
                    $value = $this->em->getRepository(Blob::class)->find($data->getValue());
                }

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
