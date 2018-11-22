<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Translation\PluralizationRules;

// This class_exists check is needed because when doing a schema check,
// doctrine will try to load this source file. But the Language class
// is compiled in to bootstrap.php so we'd end up with a dupe error
if (!class_exists('Application\DeskPRO\Entity\Language', false)) {

    /**
     * A language groups phrases and defines a locale code.
     */
    class Language extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
    {
        /**
         * The unique ID.
         *
         * @var int
         */
        protected $id = null;

        /**
         * The unique sys name assigned to the language.
         *
         * @var string
         */
        protected $sys_name = '';

        /**
         * Title of the language.
         *
         * @var string
         */
        protected $title = '';

        /**
         * Title of the language.
         *
         * @var string
         */
        protected $titleLocal = '';

        /**
         * The base filepath for default phrases for this lang.
         *
         * @var string
         */
        protected $base_filepath;

        /**
         * The locale code.
         *
         * @var string
         */
        protected $locale = 'en_US';

        /**
         * String path to image.
         *
         * @var string
         */
        protected $flag_image = '';

        /**
         * True if this is a right-to-left language.
         *
         * @var bool
         */
        protected $is_rtl = false;

        /**
         * True if has user.
         *
         * @var bool
         */
        protected $has_user = true;

        /**
         * True if has agent.
         *
         * @var bool
         */
        protected $has_agent = true;

        /**
         * True if has admin.
         *
         * @var bool
         */
        protected $has_admin = true;

        /**
         * @var array
         */
        protected $pluralCategories = ['one', 'other'];

        /**
         * A formula using "n" being an integer. This formula should be compatible with most
         * C-based languages that support ternary operators. Booleans should be cast to
         * an integer (i.e. true = 1, false = 0).
         *
         * Given an integer n, this formular returns the plural form to use for that number.
         * The plural form can be used as the index to find the category name.
         *
         * @var string
         */
        protected $pluralFormula = 'n != 1';

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        public function getUrlCode()
        {
            // special handling for 'default' to be just 'en'
            if ($this->sys_name === 'default') {
                return 'en';
            } else {
                return $this->locale;
            }
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            return $this->title;
        }

        /**
         * @return string
         */
        public function getTitleLocal()
        {
            return $this->titleLocal ?: $this->title;
        }

        /**
         * @param string $titleLocal
         *
         * @return Language
         */
        public function setTitleLocal($titleLocal)
        {
            $this->setModelField('titleLocal', $titleLocal);

            return $this;
        }

        /**
         * @return array
         */
        public function getPluralCategories()
        {
            return $this->pluralCategories ?: [];
        }

        /**
         * @param array $pluralCategories
         *
         * @return Language
         */
        public function setPluralCategories(array $pluralCategories)
        {
            $this->setModelField('pluralCategories', $pluralCategories);

            return $this;
        }

        /**
         * @param int $pluralForm A plural form (0-6). This is the plural form
         *                        returned from the formula. This formula is the same
         *                        used by most PO-style libraries too
         *
         * @return mixed
         */
        public function getPluralCategoryForForm($pluralForm)
        {
            if (isset($this->pluralCategories[$pluralForm])) {
                return $this->pluralCategories[$pluralForm];
            }
            // find the 'other' category, fallback to first element or false)
            $otherKey = array_search('other', $this->pluralCategories);

            return $otherKey ? $this->pluralCategories[$otherKey] : reset($this->pluralCategories);
        }

        /**
         * @return string
         */
        public function getPluralFormula()
        {
            return $this->pluralFormula;
        }

        /**
         * @param string $pluralFormula
         *
         * @return Language
         */
        public function setPluralFormula($pluralFormula)
        {
            $this->setModelField('pluralFormula', $pluralFormula);

            return $this;
        }

        /**
         * @return int
         */
        public function getNumPluralForms()
        {
            return count($this->pluralCategories);
        }

        /**
         * Get the plural form to use for the given integer.
         *
         * @param int $int
         *
         * @return int
         */
        public function selectPluralForm($int)
        {
            // Using the symfony lib here; it's already PHP and will be faster than eval()'ing the formula
            return PluralizationRules::get((int) $int, $this->getLocaleLanguage());
        }

        /**
         * Get the plural category to use for the given integer.
         *
         * @param int $int
         *
         * @return string
         */
        public function selectPluralCategory($int)
        {
            $form = $this->selectPluralForm($int);

            return $this->getPluralCategoryForForm($form);
        }

        /**
         * Return the language part of the locale. E.g. en-US returns en.
         *
         * @return string
         */
        public function getLocaleLanguage()
        {
            $parts = explode('-', $this->locale);

            return $parts[0];
        }

        /**
         * Get the locale code in standard IETF format (xx-XX).
         * This is the standard format to use most of the time (e.g. browser headers).
         *
         * @return string
         */
        public function getLocale()
        {
            return $this->locale;
        }

        /**
         * Gets the locale code in POSIX (ISO 15897) format (xx_XX).
         * This is the format used in Unix and some tools might use it (e.g. gettext).
         *
         * @return string
         */
        public function getPosixLocale()
        {
            if (strlen($this->locale) > 2) {
                return preg_replace('/^([a-z]{2,3})-([A-Z]{2})/', '$1_$2', $this->locale);
            }

            return $this->locale;
        }

        /**
         * Sets the locale code. The locale is expected to be 'xx' or 'xx-XX'.
         *
         * This will auto-correct 'xx_XX' to 'xx-XX'.
         *
         * @param string $locale
         *
         * @return $this
         */
        public function setLocale($locale)
        {
            if (strlen($locale) > 2) {
                $locale = preg_replace('/^([a-z]{2})_([A-Z]{2})/', '$1-$2', $locale);
            }
            $this->setModelField('locale', $locale);

            return $this;
        }

        /**
         * @param string $sys_name
         *
         * @return $this
         */
        public function setSysName($sys_name)
        {
            $this->setModelField('sys_name', $sys_name);

            return $this;
        }

        /**
         * @return string
         */
        public function getSystemName()
        {
            return $this->sys_name;
        }

        /**
         * @return bool
         */
        public function isRtl()
        {
            return $this->is_rtl;
        }

        /**
         * @return string "RTL" for right-to-left or "LTR" for left-to-right
         */
        public function getDirection()
        {
            return $this->is_rtl ? 'RTL' : 'LTR';
        }

        /**
         * @return bool
         */
        public function hasUser()
        {
            return $this->has_user;
        }

        /**
         * @return bool
         */
        public function hasAgent()
        {
            return $this->has_agent;
        }

        /**
         * @return bool
         */
        public function hasAdmin()
        {
            return $this->has_admin;
        }

        /**
         * @return string
         */
        public function getFlagImage()
        {
            return $this->flag_image ?: 'locale_'.$this->locale.'.png';
        }

        /**
         * {@inheritdoc}
         */
        public function getPhraseName($property)
        {
            return 'user.lang.lang_title_'.$this->sys_name;
        }

        /**
         * {@inheritdoc}
         */
        public function getPhraseDefault($property, Translate $translate)
        {
            return $this->title;
        }

        //###########################################################################
        // Doctrine Metadata
        //###########################################################################

        public static function loadMetadata(ClassMetadata $metadata)
        {
            $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
            $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Language';
            $metadata->setPrimaryTable(['name' => 'languages']);
            $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
            $metadata->mapField(
                [
                    'fieldName'  => 'id',
                    'type'       => 'integer',
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'id',
                    'id'         => true,
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'sys_name',
                    'type'       => 'string',
                    'length'     => 100,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'sys_name',
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'title',
                    'type'       => 'string',
                    'length'     => 255,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'title',
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'titleLocal',
                    'type'       => 'string',
                    'length'     => 255,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => true,
                    'columnName' => 'title_local',
                    'options'    => ['default' => ''],
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'base_filepath',
                    'type'       => 'string',
                    'length'     => 255,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => true,
                    'columnName' => 'base_filepath',
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'locale',
                    'type'       => 'string',
                    'length'     => 8,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'locale',
                ]
            );
            $metadata->mapField(
                [
                    'fieldName'  => 'flag_image',
                    'type'       => 'string',
                    'length'     => 50,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'flag_image',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'is_rtl',
                    'type'       => 'boolean',
                    'nullable'   => false,
                    'columnName' => 'is_rtl',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'has_user',
                    'type'       => 'boolean',
                    'nullable'   => false,
                    'columnName' => 'has_user',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'has_agent',
                    'type'       => 'boolean',
                    'nullable'   => false,
                    'columnName' => 'has_agent',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'has_admin',
                    'type'       => 'boolean',
                    'nullable'   => false,
                    'columnName' => 'has_admin',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'pluralCategories',
                    'type'       => 'simple_array',
                    'length'     => 255,
                    'nullable'   => true,
                    'columnName' => 'plural_categories',
                ]
            );

            $metadata->mapField(
                [
                    'fieldName'  => 'pluralFormula',
                    'type'       => 'string',
                    'length'     => 255,
                    'nullable'   => false,
                    'columnName' => 'plural_formula',
                    'options'    => ['default' => 'n != 1'],
                ]
            );

            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        }
    }
}
