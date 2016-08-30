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

/**
 * DeskPRO.
 *
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Form\Type\PersonPhoneNumbersType;

/**
 * Class CsvImport.
 */
class CsvImport extends AbstractJob
{
    /**
     * @var \Application\DeskPRO\Entity\CustomDefPerson[]
     */
    protected $_custom_fields;

    /**
     * @var array
     */
    protected static $options = [
        'delimeter' => [
            'comma'     => ',',
            'semicolon' => ';',
        ],
        'enclosure' => [
            'none'   => null,
            'quotes' => '"',
        ],
    ];

    /**
     * @var array
     */
    protected static $defaults = [
        'delimeter' => 'comma',
        'enclosure' => 'quotes',
    ];

    /**
     * @param array $options
     *
     * @return array
     */
    public static function getOptions(array $options = [])
    {
        foreach ($options as $k => $v) {
            if (!isset(self::$options[$k]) || !isset(self::$options[$k][$v])) {
                unset($options[$k]);
            }
        }

        $options = array_merge(self::$defaults, $options);
        foreach ($options as $k => &$v) {
            $v = self::$options[$k][$v];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        if ($this->_data['user_filename']) {
            return 'CSV Import: '.$this->_data['user_filename'];
        } else {
            return 'CSV Import';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _getDefaultData()
    {
        return [
            'blob_id'            => false,
            'field_maps'         => false,
            'new_custom_map'     => false,
            'skip_first'         => true,
            'update_if_exists'   => true,
            'welcome_email'      => false,
            'welcome_from_name'  => '',
            'welcome_from_email' => '',
            'welcome_subject'    => '',
            'welcome_message'    => '',
            'imported'           => 0,
            'failed'             => 0,
            'lines_done'         => 0,
            'fseek'              => 0,
            'user_filename'      => '',
            'log_blob_id'        => 0,
            'log'                => [],
            'ref'                => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function run($max_time)
    {
        $tmpDir = App::$container->get('deskpro.app_env')->getUserTmpDir();
        $blob   = App::getOrm()->find('DeskPRO:Blob', $this->_data['blob_id']);

        $csv_file = $tmpDir.'/blob-'.$blob->getId().'.csv';

        if (!file_exists($csv_file) || !is_readable($csv_file)) {
            file_put_contents($csv_file, App::getContainer()->getBlobStorage()->copyBlobRecordToString($blob));
        }

        if (!file_exists($csv_file) || !is_readable($csv_file)) {
            throw new \Exception("CSV file $csv_file does not exist or is not readable");
        }

        $logStore = $this->getLogStore($blob);

        if ($this->_data['new_custom_map'] === false) {
            $this->_createNewCustomFields();
        }

        $this->_custom_fields = App::getEntityRepository('DeskPRO:CustomDefPerson')->getTopFields();

        $start_time = microtime(true);

        $options = self::getOptions($this->_data['options']);
        $fp      = fopen($csv_file, 'r');
        fseek($fp, $this->_data['fseek']);

        if ($this->_data['fseek'] == 0 && $this->_data['skip_first']) {
            // skip the first row - it's labels
            fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
        }

        $complete = false;
        $imported = 0;
        $skipped  = 0;

        while ($imported < 500 && (microtime(true) - $start_time < $max_time)) {
            if (feof($fp)) {
                $complete = true;
                break;
            }

            $row = fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
            if (!$row) {
                $complete = true;
                break;
            }

            ++$this->_data['lines_done'];

            if ($this->_importRow($row)) {
                ++$this->_data['imported'];
                ++$imported;
            } else {
                ++$skipped;
            }
        }

        $this->_data['fseek'] = ftell($fp);

        fclose($fp);

        if ($this->getLogger()) {
            $this->getLogger()->logDebug("Imported $imported people");
        }

        $task               = $this->getTask();
        $task['run_status'] = 'Processed '.$this->_data['lines_done'].' entries, imported '.$this->_data['imported'].' people';
        $task['task_data']  = array_merge($task['task_data'], $this->_data);

        $logStore->setData('skipped', $logStore->getData('skipped') + $skipped);
        $logStore->setData('imported', $logStore->getData('imported') + $imported);

        if ($complete) {
            $logStore->setData('finished', time());
            $tmpFile = $tmpDir.'/blob-import-log-'.$task['id'].'.csv';
            if ($task['task_data']['log'] && ($fp = fopen($tmpFile, 'w'))) {
                foreach ($task['task_data']['log'] as $logEntry) {
                    fputcsv($fp, $logEntry, $options['delimeter'], $options['enclosure']);
                }
                fclose($fp);
                $logBlob = App::getContainer()->getBlobStorage()->createBlobRecordFromFile(
                    $tmpFile,
                    'import-log-'.$task['id'].'.csv',
                    'text/csv'
                );
                $task['task_data'] = array_merge(
                    $task['task_data'],
                    ['log' => null, 'log_blob_id' => $logBlob['id']]
                );
                @unlink($tmpFile);
            }

            @unlink($csv_file);
            try {
                App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
            } catch (\Exception $e) {
            }

            return self::TASK_COMPLETED;
        } else {
            return self::TASK_CONTINUING;
        }
    }

    /**
     * @param array $errors
     */
    protected function log(array $errors)
    {
        ++$this->_data['failed'];
        $this->_data['log'][] = $errors;
    }

    protected function _createNewCustomFields()
    {
        $this->_data['new_custom_map'] = [];
        $handlers                      = [
            'text'     => 'Application\DeskPRO\CustomFields\Handler\Text',
            'textarea' => 'Application\DeskPRO\CustomFields\Handler\Textarea',
            'choice'   => 'Application\DeskPRO\CustomFields\Handler\Choice',
            'date'     => 'Application\DeskPRO\CustomFields\Handler\Date',
        ];

        foreach ($this->_data['field_maps'] as $column_id => $info) {
            if (!isset($info['map'])) {
                continue;
            }
            if (!isset($handlers[@$info['handler_class']])) {
                continue;
            }
            if ($info['map'] == 'new_custom') {
                $field                = new \Application\DeskPRO\Entity\CustomDefPerson();
                $field->title         = $info['title'];
                $field->handler_class = $handlers[$info['handler_class']];
                $field->display_order = $column_id;

                App::getOrm()->persist($field);
                App::getOrm()->flush();

                $this->_data['new_custom_map'][$column_id] = $field->id;
            }
        }
    }

    /**
     * @param array $row
     *
     * @return int
     */
    protected function _importRow(array $row)
    {
        $field_maps = $this->_data['field_maps'];

        if (isset($row[0]) && $row[0] === null) {
            $this->log(['Empty row']);

            return false;
        }

        $em = App::getOrm();

        /** @var $person_em \Application\DeskPRO\EntityRepository\Person */
        $person_em = App::getEntityRepository('DeskPRO:Person');

        /** @var $organization_em \Application\DeskPRO\EntityRepository\Organization */
        $organization_em = App::getEntityRepository('DeskPRO:Organization');

        $person = new Person();

        $primary_email    = false;
        $password         = false;
        $secondary_emails = [];
        $addresses        = [];
        $errors           = [];
        $send_welcome     = true;

        foreach ($field_maps as $column_id => $info) {
            if (empty($info['map'])) {
                continue;
            }

            if (!isset($row[$column_id])) {
                continue;
            }

            $column_value = $row[$column_id];
            if ($column_value === '') {
                continue;
            }

            if ($info['map'] == 'primary_email') {
                if (!\Orb\Validator\StringEmail::isValueValid($column_value)) {
                    $errors[] = sprintf('Invalid email %s', $column_value);
                    continue;
                }
                if (App::$container->getEmailAccountManager()->findAccountForEmailAddress($column_value)) {
                    $errors[] = sprintf('Email %s already exist', $column_value);
                    continue;
                }

                if ($old = $person_em->findOneByEmail($column_value)) {
                    if (!$this->_data['update_if_exists']) {
                        $errors[] = sprintf('Email %s already exist', $column_value);
                        continue;
                    }
                    $person       = $old;
                    $send_welcome = false;
                }

                $primary_email = strtolower($column_value);
            } elseif ($info['map'] == 'secondary_email') {
                if (!\Orb\Validator\StringEmail::isValueValid($column_value)) {
                    $errors[] = sprintf('Invalid email %s', $column_value);
                    break;
                }
                if (App::$container->getEmailAccountManager()->findAccountForEmailAddress($column_value)) {
                    $errors[] = sprintf('Email %s already exist', $column_value);
                    break;
                }
                if ($old = $person_em->findOneByEmail($column_value)) {
                    if (!$this->_data['update_if_exists']) {
                        $errors[] = sprintf('Email %s already exist', $column_value);
                        break;
                    }
                    $person       = $old;
                    $send_welcome = false;
                }

                $secondary_emails[] = strtolower($column_value);
            }
        }

        if (!$primary_email) {
            $primary_email = array_shift($secondary_emails);
        }

        if (!$primary_email && !$person->getId()) {
            $this->log($errors);

            return false;
        }

        $emails = array_flip($person->getEmailAddresses());
        !isset($emails[$primary_email]) && $person->addEmailAddressString($primary_email);

        array_unique($secondary_emails);
        foreach ($secondary_emails as $secondary_email) {
            if ($secondary_email == $primary_email) {
                continue;
            }
            !isset($emails[$secondary_email]) && $person->addEmailAddressString($secondary_email);
        }

        $isNew = !$person->getId();

        foreach ($field_maps as $column_id => $info) {
            if (empty($info['map'])) {
                continue;
            }

            $column_value = $row[$column_id];
            if ($column_value === '') {
                continue;
            }

            $map_field     = $info['map'];
            $label         = isset($info['label']) ? $info['label'] : '';
            $info['label'] = $label;

            switch ($map_field) {
                case 'first_name':
                case 'last_name':
                case 'name':
                case 'title_prefix':
                case 'organization_position':
                    $person->$map_field = $column_value;
                    break;

                case 'password':
                    $password = $column_value;
                    break;

                case 'organization':
                    $organization = $organization_em->findOneByName($column_value);
                    if ($organization) {
                        $person->setOrganization($organization);
                    } elseif (!empty($info['create_auto'])) {
                        $organization       = new \Application\DeskPRO\Entity\Organization();
                        $organization->name = $column_value;

                        $em->persist($organization);

                        $person->setOrganization($organization);
                    }
                    break;

                case 'website':
                    $this->_addContactData($person, 'website', ['url' => $column_value], $label);
                    break;

                case 'twitter':
                    $this->_addContactData($person, 'twitter', ['username' => $column_value], $label);
                    break;

                case 'linkedin':
                    $this->_addContactData($person, 'linkedin', ['profile_url' => $column_value], $label);
                    break;

                case 'facebook':
                    $this->_addContactData($person, 'facebook', ['profile_url' => $column_value], $label);
                    break;

                case 'phone':
                    $form = App::$container->getFormFactory()->create(new PersonPhoneNumbersType(), $person);
                    $form->submit(['phone_numbers' => [['number' => $column_value]]]);
                    if (!$form->isValid()) {
                        $this->log([sprintf('Invalid phone number "%s"', $column_value)]);
                        // todo
                        foreach ($person->phone_numbers as $pn) {
                            if (!$pn['number']) {
                                $person->phone_numbers->removeElement($pn);
                            }
                        }
                    } else {
                        // todo wtf?!
                        foreach ($person->phone_numbers as $pn) {
                            $pn->person = $person;
                        }
                    }

                    break;

                case 'im':
                    if (empty($info['type'])) {
                        $info['type'] = 'aim';
                    }
                    $this->_addContactData($person, 'instant_message', ['service' => $info['type'], 'username' => $column_value], $label);
                    break;

                case 'address':
                case 'address1':
                case 'address2':
                case 'city':
                case 'state':
                case 'post_code':
                case 'country':
                    if (!isset($addresses[$info['label']])) {
                        $addresses[$info['label']] = ['label' => $info['label']];
                    }
                    $addresses[$info['label']][$map_field] = $column_value;
                    break;

                case 'language':
                    $language = is_numeric($column_value)
                        ? $em->find('DeskPRO:Language', $column_value)
                        : $em->getRepository('DeskPRO:Language')->getByTitle($column_value);

                    if ($language) {
                        $person->language = $language;
                    }

                    break;

                default:
                    $custom_field_id = false;
                    if (preg_match('/^custom_(\d+)$/', $map_field, $match)) {
                        $custom_field_id = $match[1];
                        $new_on_unknown  = !empty($info['new_on_unknown']);
                    } elseif ($map_field == 'new_custom' && isset($this->_data['new_custom_map'][$column_id])) {
                        $custom_field_id = $this->_data['new_custom_map'][$column_id];
                        $new_on_unknown  = true;
                    }

                    if (!$column_value) {
                        continue;
                    }

                    if ($custom_field_id && isset($this->_custom_fields[$custom_field_id])) {
                        $custom_field = $this->_custom_fields[$custom_field_id];
                        if ($custom_field->isChoiceType()) {
                            $selected_childs = [];
                            $test_value      = mb_strtolower($column_value);
                            $multiple        = !empty($custom_field['options']['multiple']);

                            // find an existing option by title
                            foreach ($custom_field->getAllChildren() as $child_field) {
                                if (mb_strtolower($child_field->getTitle()) === $test_value) {
                                    $selected_childs[$child_field['id']] = $child_field;
                                    if (!$multiple) {
                                        break;
                                    }
                                }
                            }

                            // create a new one if necessary
                            if (!$selected_childs && $new_on_unknown) {
                                $selected_child                = new \Application\DeskPRO\Entity\CustomDefPerson();
                                $selected_child->title         = $column_value;
                                $selected_child->display_order = count($custom_field->getAllChildren()) + 1;
                                $custom_field->addChild($selected_child);
                                $em->persist($selected_child);
                                $selected_childs[] = $selected_child;
                            }

                            // associate it
                            if ($selected_childs) {
                                $person->custom_data->clear();
                                foreach ($selected_childs as $child) {
                                    $custom_data             = new \Application\DeskPRO\Entity\CustomDataPerson();
                                    $custom_data->person     = $person;
                                    $custom_data->field      = $child;
                                    $custom_data->root_field = $custom_field;
                                    $custom_data->value      = 1;
                                    $em->persist($custom_data);
                                    $person->addCustomData($custom_data);
                                }
                            }
                        } elseif ($custom_field->getTypeName() == 'date') {
                            if (ctype_digit($column_value)) {
                                // assume timestamp
                                $set_field = true;
                            } elseif (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $column_value)) {
                                $set_field = true;

                                $date = \DateTime::createFromFormat('Y-m-d', $column_value,
                                    new \DateTimeZone(App::getSetting('core.default_timezone'))
                                );
                                $date = \Orb\Util\Dates::convertToUtcDateTime($date);

                                $column_value = $date->getTimestamp();
                            } else {
                                $set_field = false;
                            }

                            if ($set_field) {
                                $custom_data             = new \Application\DeskPRO\Entity\CustomDataPerson();
                                $custom_data->person     = $person;
                                $custom_data->field      = $custom_field;
                                $custom_data->root_field = $custom_field;
                                $custom_data->value      = $column_value;

                                $em->persist($custom_data);
                                $person->addCustomData($custom_data);
                            }
                        } else {
                            $custom_data             = new \Application\DeskPRO\Entity\CustomDataPerson();
                            $custom_data->person     = $person;
                            $custom_data->field      = $custom_field;
                            $custom_data->root_field = $custom_field;
                            $custom_data->value      = 0;
                            $custom_data->input      = $column_value;

                            $em->persist($custom_data);
                            $person->addCustomData($custom_data);
                        }
                    }
            }
        }

        if ($password === false) {
            $password = \Orb\Util\DpStrings::random(10);
        }
        $person->setPassword($password);

        foreach ($addresses as $address) {
            if (!isset($address['address'])) {
                $address['address'] = trim((isset($address['address1']) ? $address['address1'] : '')."\n".(isset($address['address2']) ? $address['address2'] : ''));
            }
            $this->_addContactData($person, 'address', $address, $address['label']);
        }

        $em->persist($person);
        $em->flush();
        if ($isNew) {
            $label = new LabelPerson();
            $label->setLabel('import-'.$this->_data['ref']);
            $label->person = $person;
            $em->persist($label);
            $em->flush($label);
        }

        if ($this->_data['welcome_email'] && $send_welcome) {
            $mailer = App::getContainer()->getMailer();

            $message = $mailer->createMessage();
            $message->setToPerson($person);
            $message->setTemplate('DeskPRO:emails_user:register-welcome-byagent.html.twig', ['person' => $person]);
            App::$container->getTranslator()->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                $message->prepare();
            });

            $mailer->send($message);
        }

        return $person->id;
    }

    /**
     * @param string $message
     * @param Person $person
     *
     * @return mixed
     */
    protected function _replaceMessagePlaceholders($message, Person $person)
    {
        $message = preg_replace_callback('/\{\{\s*([a-z0-9_-]+)\s*\}\}/i', function ($match) use ($person) {
            switch (strtolower($match[1])) {
                case 'name': return $person->getDisplayName();
                case 'email': return $person->getPrimaryEmailAddress();
                case 'password': return $person->getPlaintextPassword();
                default: return $match[0];
            }
        }, $message);

        return $message;
    }

    /**
     * @param Person $person
     * @param string $type
     * @param array  $data
     * @param null   $comment
     *
     * @return PersonContactData|void
     */
    protected function _addContactData(Person $person, $type, array $data, $comment = null)
    {
        if ($comment !== null) {
            $data['comment'] = $comment;
        }

        $contact               = new \Application\DeskPRO\Entity\PersonContactData();
        $contact->contact_type = $type;
        $contact->applyFormData($data);

        foreach ($person->contact_data as $cd) {
            // todo?
            /** @var $cd PersonContactData */
            if (mb_strtolower($cd->getSearchString()) === mb_strtolower($contact->getSearchString())) {
                return;
            }
        }

        $contact->person = $person;
        App::getOrm()->persist($contact);

        return $contact;
    }

    /**
     * @param Blob $blob
     *
     * @return DataStore|null
     */
    protected function getLogStore(Blob $blob)
    {
        $em = App::getOrm();

        /** @var \Application\DeskPRO\EntityRepository\DataStore $rep */
        $rep   = $em->getRepository(DataStore::class);
        $task  = $this->getTask();
        $store = null;

        if ($ref = @$task['task_data']['ref']) {
            $store = $rep->findOneBy(['name' => 'csv_import.'.$ref]);
        }

        if (!$store) {
            if (!$ref) {
                if ($stores = $rep->getByPrefix('csv_import.'.date('Ymd'))) {
                    $last = end($stores);
                    $ref  = substr($last['name'], 11, -3).sprintf('%03d', (int) substr($last['name'], -3) + 1);
                } else {
                    $ref = date('Ymd').'-001';
                }
                $this->_data['ref'] = $ref;
            }

            $store         = new DataStore();
            $store['name'] = 'csv_import.'.$ref;
            $store->setData('file', $blob['filename']);
            $store->setData('started', time());
            $store->setData('finished', 0);
            $store->setData('skipped', 0);
            $store->setData('imported', 0);
            $em->persist($store);
        }

        return $store;
    }
}
