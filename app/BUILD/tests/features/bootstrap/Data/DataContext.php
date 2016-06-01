<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat\Data;

use Application\DeskPRO\Domain\DomainObject;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DpBehat\BaseContext;

/**
 * Class DataContext.
 *
 * This context is responsible for domain objects creation, including handling references between created objects, and
 * for various placeholders handling such as object IDs.
 */
class DataContext extends BaseContext
{
    /**
     * @var array Map of string reference names to actual objects
     */
    private static $references = [];

    /**
     * @var array Map of string placeholder names to string placeholder values
     */
    private static $placeholders = [];

    /**
     * @BeforeScenario
     */
    public function ensureOm()
    {
        try {
            $this->om();
        } catch (\Exception $e) {
            self::initOm();
        }
    }

    /**
     * @param string $name
     * @param object $object
     */
    public static function setReference($name, $object)
    {
        self::$references[$name] = $object;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasReference($name)
    {
        return array_key_exists($name, self::$references);
    }

    /**
     * Clears references and placeholders.
     */
    public static function clear()
    {
        self::$references   = [];
        self::$placeholders = [];
    }

    /**
     * @param string $name
     * @param bool   $throwIfMissing
     *
     * @throws \Exception
     *
     * @return object|null
     */
    public static function getReference($name, $throwIfMissing = true)
    {
        if (!array_key_exists($name, self::$references)) {
            if ($throwIfMissing) {
                throw new \Exception("Unknown reference $name");
            }

            return;
        }

        if (self::$references[$name] instanceof DomainObject || self::$references[$name] instanceof EntityInterface) {
            return self::getEm()->merge(self::$references[$name]);
        }

        return self::$references[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function setPlaceholder($name, $value)
    {
        self::$placeholders[$name] = $value;
    }

    /**
     * @param string $name
     * @param bool   $throwIfMissing
     *
     * @throws \Exception
     *
     * @return object|null
     */
    public static function getPlaceholder($name, $throwIfMissing = true)
    {
        if (!array_key_exists($name, self::$placeholders)) {
            if ($throwIfMissing) {
                throw new \Exception("Unknown placeholder $name");
            }

            return;
        }

        return self::$placeholders[$name];
    }

    /**
     * @param string $content
     * @param bool   $isJson
     *
     * @return string
     */
    public static function replace($content, $isJson = false)
    {
        $initial = $content;
        $content = self::replacePlaceholders($content, $isJson);
        $content = self::replaceReferences($content, $isJson);

        // Print to logs to verify substitution worked as expected
        if ($initial != $content) {
            echo $content;
        }

        return $content;
    }

    /**
     * @Given there are no :type records
     * @Given no :type records exist
     * @Given I remove all :type records
     */
    public function noRecordsExist($type)
    {
        $records = $this->om()->locate($type);
        foreach ($records as $record) {
            $this->em()->remove($record);
        }
        $this->em()->flush();
    }

    /**
     * @Given I add a(n) :type record and reference it as :ref
     * @Given I add a(n) :type and reference it as :ref
     * @Given I create a(n) :type record and reference it as :ref
     * @Given I create a(n) :type and reference it as :ref
     * @Given I have a(n) :type record referenced as :ref
     */
    public function iCreateAnObjectAndReferenceItAs($type, $ref)
    {
        $record = $this->om()->create($type, []);
        $this->persistAndFlush($record);

        self::setReference($ref, $record);
    }

    /**
     * @Given I create a(n) :type with :prop equal to :value and reference it as :ref
     * @Given I add a(n) :type record with :prop equal to :value referenced as :ref
     * @Given I have a(n) :type record with :prop equal to :value referenced as :ref
     * @Given I have a(n) :type record with :prop equal to :value which is referenced as :ref
     */
    public function iCreateAnObjectWithPropEqualToAndReferenceItAs($type, $prop, $value, $ref)
    {
        $this->theFollowingRecordsExist($type, new TableNode([['#', $prop], [$ref, $value]]));
    }

    /**
     * @Given the only :type has :prop equal to :value and referenced as :ref
     */
    public function theOnlyObjectWithPropEqualToIsReferencedAs($type, $prop, $value, $ref)
    {
        $this->noRecordsExist($type);
        $this->iCreateAnObjectWithPropEqualToAndReferenceItAs($type, $prop, $value, $ref);
    }

    /**
     * @Given the following :type records exist:
     * @Given I add the following :type records:
     * @Given I have the following :type records:
     * @Given I have this :type records:
     */
    public function theFollowingRecordsExist($type, TableNode $table)
    {
        $recordsData = $table->getHash();
        foreach ($recordsData as $data) {
            // Remember reference and don't pass it to the factory
            $reference = false;
            if (array_key_exists('#', $data)) {
                $reference = $data['#'];
                unset($data['#']);
            }

            // Resolve references to other objects
            foreach ($data as &$value) {
                $value = self::isReference($value) ? self::resolveReference($value) : $value;
            }

            $record = $this->om()->create($type, $data);

            // Track the record reference
            if ($reference) {
                self::$references[$reference] = $record;
            }

            $this->em()->persist($record);
            $this->em()->flush();
        }
    }

    /**
     * @Given only the following :type records exist:
     */
    public function onlyTheFollowingRecordsExist($type, TableNode $table)
    {
        $this->noRecordsExist($type);
        $this->theFollowingRecordsExist($type, $table);
    }

    /**
     * @param string $ref
     *
     * @throws \Exception
     *
     * @return object
     */
    private static function resolveReference($ref)
    {
        $ref = str_replace(['{', '}', '~'], '', $ref);
        if (!array_key_exists($ref, self::$references)) {
            throw new \Exception("Unable to resolve reference '$ref'");
        }

        return self::getReference($ref);
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    private static function isReference($string)
    {
        if (empty($string)) {
            return false;
        }

        $last = strlen($string) - 1;

        return ($string[0] === '{' && $string[$last] === '}') || ($string[0] === '~' && $string[$last] === '~');
    }

    /**
     * @param string $content
     * @param bool   $isJson
     *
     * @return string
     */
    private static function replacePlaceholders($content, $isJson)
    {
        foreach (self::$placeholders as $name => $value) {
            if (!$isJson) {
                $content = str_replace('{'.$name.'}', $value, $content);
            }
            $content = str_replace('~'.$name.'~', $value, $content);
        }

        return $content;
    }

    /**
     * @param string $content
     * @param bool   $isJson
     *
     * @throws \Exception
     *
     * @return string
     */
    private static function replaceReferences($content, $isJson)
    {
        $callback = function ($matches) {
            if (strpos($matches[0], ':')) {
                list($ref, $prop) = explode(':', $matches[1], 2);
            } else {
                $ref  = $matches[0];
                $prop = 'id';
            }

            $object = self::resolveReference($ref);

            $reflectionObject = new \ReflectionObject($object);
            if (!$reflectionObject->hasProperty($prop)) {
                throw new \Exception("Property $prop doesn't exist");
            }
            $reflectionProperty = $reflectionObject->getProperty($prop);
            $reflectionProperty->setAccessible(true);

            return $reflectionProperty->getValue($object);
        };

        if (!$isJson) {
            $content = preg_replace_callback('/\{(.+)\}/U', $callback, $content);
        }
        $content = preg_replace_callback('/\~(.+)\~/U', $callback, $content);

        return $content;
    }

    // ConcreteDataContext ---------------------------------------------------------------------------------------------

    /**
     * @Given there are no Blob records in the DB
     */
    public function noBlobs()
    {
        $this->noRecordsExist('TaskAttachment');
        $this->noRecordsExist('Blob');
    }

    /**
     * @Given there are no custom ticket fields defined
     */
    public function noCustomTicketFieldsExist()
    {
        $this->noRecordsExist('CustomDefTicket');
    }

    /**
     * @Given the following custom ticket fields exist:
     */
    public function theFollowingCustomTicketFieldsExist($table)
    {
        $this->theFollowingRecordsExist('CustomDefTicket', $table);
    }

    /**
     * @Given only the following custom ticket fields exist:
     */
    public function onlyTheFollowingCustomTicketFieldsExist($table)
    {
        $this->onlyTheFollowingRecordsExist('CustomDefTicket', $table);
    }

    /**
     * @Given only the following custom organization fields exist:
     */
    public function onlyTheFollowingCustomOrganizationFieldsExist($table)
    {
        $this->onlyTheFollowingRecordsExist('CustomDefOrganization', $table);
    }
}
