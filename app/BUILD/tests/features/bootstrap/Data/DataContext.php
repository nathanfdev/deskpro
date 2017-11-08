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

namespace DpBehat\Data;

use Application\DeskPRO\Entity\Ticket;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\Common\Collections\ArrayCollection;
use DpBehat\BaseContext;
use DpBehat\Data\Factory\SimpleFactory;
use DpBehat\Data\PeopleContext as PeopleDataContext;
use DpBehat\DataSetContext;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class DataContext.
 *
 * This context is responsible for domain objects creation, including handling references between created objects, and
 * for various placeholders handling such as object IDs.
 */
class DataContext extends BaseContext
{
    /**
     * @var DataSetContext
     */
    private $dataSetContext;

    /**
     * @var PeopleDataContext
     */
    private $peopleDataContext;

    /**
     * @var array Map of string reference names to actual objects
     */
    private static $references = [];

    /**
     * @var array Map of string placeholder names to string placeholder values
     */
    private static $placeholders = [];

    /**
     * @var bool
     */
    private static $isTheFirstSuiteScenario = true;

    /**
     * @var bool
     */
    private static $isNew = false;

    /**
     * @var bool
     */
    private static $needCleanup = false;

    /**
     * @var string
     */
    private static $setName;

    /**
     * @BeforeSuite
     */
    public static function onBeforeSuite(BeforeSuiteScope $scope)
    {
        switch ($scope->getSuite()->getName()) {
            case 'api':
                self::$setName = 'api';
                break;
            default:
                self::$setName = 'fresh';
                break;
        }
    }

    /**
     * @BeforeFeature
     *
     * @param BeforeFeatureScope $scope
     */
    public static function checkNew(BeforeFeatureScope $scope)
    {
        if ($scope->getFeature()->hasTag('new')) {
            self::$isNew = true;
        }
    }

    /**
     * Schedule DB cleanup before next login.
     *
     * @BeforeFeature
     */
    public static function scheduleCleanup()
    {
        self::$needCleanup = true;
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment             = $scope->getEnvironment();
        $this->dataSetContext    = $environment->getContext('DpBehat\DataSetContext');
        $this->peopleDataContext = $environment->getContext('DpBehat\Data\PeopleContext');
    }

    /**
     * @BeforeScenario
     */
    public function ensureDb()
    {
        if (self::$isTheFirstSuiteScenario) {
            if (self::$isNew) {
                $statement = $this->em()->getConnection()->executeQuery('SHOW TABLES LIKE "people"');
                $statement->execute();
                if (!$statement->rowCount()) {
                    $this->dataSetContext->iInstallDataSet(self::$setName);
                }
            } else {
                $this->dataSetContext->iInstallDataSet(self::$setName);
            }

            $this->peopleDataContext->everyoneGroupExists();
            $this->peopleDataContext->registeredGroupExists();
            $this->peopleDataContext->agentAllSafePermGroupExists();
            $this->peopleDataContext->agentAllPermGroupExists();

            self::$isTheFirstSuiteScenario = false;
        }

        if (self::$needCleanup) {
            $this->cleanup();
            self::$needCleanup = false;
        }

        // re-create objects manager with new $em for each scenario
        // because kernel reboots for each scenario
        self::initOm();
    }

    /**
     * @AfterScenario
     */
    public function resetQueriesCounter()
    {
        $this->container()->get('test.queries.counter_listener')->resetSettings();
    }

    /**
     * Clean up DB.
     */
    private function cleanup()
    {
        $this->em()->getConnection()->executeQuery('
            DELETE FROM permissions_cache;
        ');
        $this->em()->clear();
        self::clear();
    }

    /**
     * Store data object ref.
     * We create entities implicit way so keep just class/id ref not full object.
     *
     * @param string $name
     * @param object $object
     *
     * @throws \Exception
     */
    public static function setReference($name, $object)
    {
        if (!is_object($object)) {
            throw new \Exception('Unable to set reference of non object');
        }
        if (!$object->getId()) {
            throw new \Exception('Unable to set reference of not persisted object');
        }

        self::$references[$name] = [get_class($object), $object->getId()];
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

        list($class, $id) = self::$references[$name];

        $entity = self::getEm()->find($class, $id);
        if (!$entity) {
            throw new \Exception("Entity for $name ref not found");
        }

        return $entity;
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
     *
     * @param string $type
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
     *
     * @param string $type
     * @param string $ref
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
     *
     * @param string $type
     * @param string $prop
     * @param string $value
     * @param string $ref
     */
    public function iCreateAnObjectWithPropEqualToAndReferenceItAs($type, $prop, $value, $ref)
    {
        $this->theFollowingRecordsExist($type, new TableNode([['#', $prop], [$ref, $value]]));
    }

    /**
     * @Given the only :type has :prop equal to :value and referenced as :ref
     *
     * @param string $type
     * @param string $prop
     * @param string $value
     * @param string $ref
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
     *
     * @param string    $type
     * @param TableNode $table
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
                if (self::isArray($value) && !json_decode($value)) {
                    $arrayValue = [];
                    foreach (self::transformToArray($value) as $item) {
                        if (self::isReference($item)) {
                            $arrayValue[] = self::resolveReference($item);
                        } else {
                            $arrayValue[] = $item;
                        }
                    }

                    $value = new ArrayCollection($arrayValue);
                } elseif (self::isReference($value)) {
                    $value = self::resolveReference($value);
                } elseif (is_string($value)) {
                    $value = self::replace($value, true);
                }
            }

            $record = $this->om()->create($type, $data);

            $this->em()->persist($record);
            $this->em()->flush();
            $this->em()->clear();

            // Track the record reference
            if ($reference) {
                $this->setReference($reference, $record);
            }
        }
    }

    /**
     * @Given only the following :type records exist:
     *
     * @param string    $type
     * @param TableNode $table
     */
    public function onlyTheFollowingRecordsExist($type, TableNode $table)
    {
        $this->noRecordsExist($type);
        $this->theFollowingRecordsExist($type, $table);
    }

    /**
     * @Given the :ref record :prop prop is equal to :value
     *
     * @param string $ref
     * @param string $prop
     * @param string $value
     */
    public function theFollowingRecordPropHasValue($ref, $prop, $value)
    {
        $record = self::resolveReference($ref);
        if (self::isReference($value)) {
            $value = self::resolveReference($value);
        } elseif (self::isArray($value)) {
            $arrayValue = [];
            foreach (self::transformToArray($value) as $item) {
                if (self::isReference($item)) {
                    $arrayValue[] = self::resolveReference($item);
                } else {
                    $arrayValue[] = $item;
                }
            }

            $value = new ArrayCollection($arrayValue);
        } else {
            $value = self::replace($value);
        }

        $value = ObjectsManager::preProcessValue($value);
        SimpleFactory::provide($record, [$prop => $value]);

        if ($record instanceof Ticket) {
            $record->disableAutoTicketProcess();
        }

        $this->em()->persist($record);
        $this->em()->flush();
        $this->em()->clear();

    }

    /**
     * @Given I set max_queries=:maxQueriesCount, max_rows=:maxFetchRows
     *
     * @param int $maxQueriesCount
     * @param int $maxFetchRows
     */
    public function setQueriesCounterSettings($maxQueriesCount, $maxFetchRows)
    {
        $this->container()->get('test.queries.counter_listener')->setMaxQueriesCount($maxQueriesCount);
        $this->container()->get('test.queries.counter_listener')->setMaxFetchRows($maxFetchRows);
    }

    /**
     * @param string $ref
     *
     * @throws \Exception
     *
     * @return object
     */
    public static function resolveReference($ref)
    {
        $ref = trim($ref, '{}~');
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
        return is_string($string) && (preg_match('/^{[\w-@.]+}$/', $string) || preg_match('/^~[\w-@.]+~$/', $string));
    }

    /**
     * @param $string
     *
     * @return bool
     */
    private static function isArray($string)
    {
        return is_string($string) && preg_match('/^\[.+\]$/', $string);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    private static function transformToArray($string)
    {
        $string = trim($string, '[]');

        return array_map('trim', explode(',', $string));
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
            if (!$isJson && !json_decode($content)) {
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
            if (!$object) {
                throw new \Exception("Object $ref not found");
            }

            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            if ($propertyAccessor->isReadable($object, $prop)) {
                return $propertyAccessor->getValue($object, $prop);
            }

            $reflectionObject = new \ReflectionObject($object);
            if (!$reflectionObject->hasProperty($prop)) {
                $propsNames = [];
                foreach ($reflectionObject->getProperties() as $prop) {
                    $propsNames[] = $prop->getName();
                }

                throw new \Exception("Property $prop doesn't exist. Has props: ".implode(',', $propsNames));
            }

            $reflectionProperty = $reflectionObject->getProperty($prop);
            $reflectionProperty->setAccessible(true);

            return $reflectionProperty->getValue($object);
        };

        if (!$isJson && !json_decode($content)) {
            $content = preg_replace_callback('/\{(.+)\}.*/U', $callback, $content);
        }

        $content = preg_replace_callback('/\~(.+)\~.*/U', $callback, $content);

        return $content;
    }
}
