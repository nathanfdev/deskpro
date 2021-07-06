<?php

/**
 * DeskPRO.
 *
 * @category Types
 */

namespace Application\DeskPRO\DBAL\Types;

use DeskPRO\Component\Util\UnserializeUtil;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class DpArrayType extends ArrayType
{
    public function getSQLDeclaration(array $fieldDeclaration, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return 'LONGBLOB';
    }

    public function convertToPHPValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        try {
            if ($value === null || $value === 'N;') {
                return;
            }

            $value = (is_resource($value)) ? stream_get_contents($value) : $value;

            // backwards compat for legacy chat widget settings in datastore table
            // that was storing a serialised array that included a class
            if (strpos($value, 'a:1:{s:14:"brand_settings";O:88:"DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\WidgetBrandSettings"') === 0) {
                $val = unserialize($value, [
                    'allowed_classes' => [
                        'Doctrine\\Common\\Collections\\ArrayCollection',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\WidgetOptions',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\WidgetBrandCommonSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\WidgetBrandSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\WidgetBrandTicketSettings',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ButtonSettings\\WidgetBrandButtonColorsSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ButtonSettings\\WidgetBrandButtonSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ButtonSettings\\WidgetBrandButtonTranslation',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ChatSettings\\WidgetBrandChatCustomField',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ChatSettings\\WidgetBrandChatPopupSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ChatSettings\\WidgetBrandChatPopupTranslation',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\BrandSettings\\ChatSettings\\WidgetBrandChatSettings',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\GlobalSettings\\WidgetGlobalChatSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\GlobalSettings\\WidgetGlobalCompanySettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\Options\\GlobalSettings\\WidgetGlobalSettings',

                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\JwtSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\WidgetSettings',
                        'DeskPRO\\Bundle\\AppBundle\\Settings\\Model\\Widget\\WidgetUrlSettings',
                    ]
                ]);
            } else {
                $val   = UnserializeUtil::unserializeArray($value);
                if ($val === false && $value != 'b:0;') {
                    throw ConversionException::conversionFailed($value, $this->getName());
                }
            }

            return $val;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getName()
    {
        return Type::TARRAY;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
