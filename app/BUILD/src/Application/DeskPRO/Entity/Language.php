<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

// This class_exists check is needed because when doing a schema check,
// doctrine will try to load this source file. But the Language class
// is compiled in to bootstrap.php so we'd end up with a dupe error
if (!class_exists('Application\DeskPRO\Entity\Language', false)) {

    /**
     * A language groups phrases and defines a locale code.
     *
     * @JMS\ExclusionPolicy("all")
     */
    class Language extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
    {
        /**
         * The unique ID.
         *
         * @JMS\Expose()
         * @JMS\Type("integer")
         *
         * @var int
         */
        protected $id = null;

        /**
         * The unique sys name assigned to the language.
         *
         * @JMS\Expose()
         * @JMS\Type("string")
         *
         * @var string
         */
        protected $sys_name = '';

        /**
         * The three-letter ISO 639-2 code.
         *
         * @JMS\Expose()
         * @JMS\Type("string")
         *
         * @var string
         */
        protected $lang_code = '';

        /**
         * Title of the language.
         *
         * @JMS\Expose()
         * @JMS\Type("string")
         *
         * @var string
         */
        protected $title = '';

        /**
         * The base filepath for default phrases for this lang.
         *
         * @var string
         */
        protected $base_filepath;

        /**
         * The locale code.
         *
         * @JMS\Expose()
         * @JMS\Type("string")
         *
         * @var string
         */
        protected $locale = 'en_US';

        /**
         * String path to image.
         *
         * @JMS\Expose()
         * @JMS\Type("string")
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
         * @JMS\Expose()
         * @JMS\Type("boolean")
         *
         * @var bool
         */
        protected $has_user = true;

        /**
         * True if has agent.
         *
         * @JMS\Expose()
         * @JMS\Type("boolean")
         *
         * @var bool
         */
        protected $has_agent = true;

        /**
         * True if has admin.
         *
         * @JMS\Expose()
         * @JMS\Type("boolean")
         *
         * @var bool
         */
        protected $has_admin = true;

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
        public function getLangCode()
        {
            return $this->lang_code;
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
        public function getLocale()
        {
            return $this->locale;
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
        public function isIsRtl()
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
                    'fieldName'  => 'lang_code',
                    'type'       => 'string',
                    'length'     => 3,
                    'precision'  => 0,
                    'scale'      => 0,
                    'nullable'   => false,
                    'columnName' => 'lang_code',
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

            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        }
    }
}
