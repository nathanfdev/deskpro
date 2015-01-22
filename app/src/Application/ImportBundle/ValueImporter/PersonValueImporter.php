<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Entity\Person;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper\MapperInterface;
use Application\ImportBundle\Value\PersonValue;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

/**
 * Class PersonValueImporter
 * @package Application\ImportBundle\ValueImporter
 */
class PersonValueImporter extends AbstractValueImporter
{
    /**
     * @var array
     */
    protected $custom_fields_array = array();

    /**
     * @var array
     */
    protected $supported_custom_field_types = array(
        'Application\DeskPRO\CustomFields\Handler\Text'
    );

    /**
     * @param mixed $pval
     * @throws \Application\ImportBundle\Exception\BadDataException
     */
    public function importValue($pval)
    {
        if (!($pval instanceof PersonValue)) {
            throw new \InvalidArgumentException("This importer can only import PersonValue");
        }

        #------------------------------
        # Validate emails
        #------------------------------

        if ($pval->emails) {
            $pval->emails = array_filter($pval->emails, function ($eml) {
                return StringEmail::isValueValid($eml);
            });
        }

        if (!$pval->emails) {
            throw new BadDataException("PersonValue must have at least one valid email");
        }

        $log_id = "Person :: " . Arrays::getFirstItem($pval->emails) . "";

        #------------------------------
        # Normalise name
        #------------------------------

        if ($pval->name) {
            $name = preg_replace('# {2,}#', ' ', $pval->name);
            if (!$pval->first_name) {
                $pval->first_name = $name[0];
            }
            if (!$pval->last_name) {
                $pval->last_name = $name[1];
            }
        }
        if (!$pval->name && ($pval->first_name || $pval->last_name)) {
            $pval->name = trim(($pval->first_name ?: '') . ($pval->last_name ?: ''));
        }

        #------------------------------
        # Find existing user
        #------------------------------

        $exist_emails = $this->getExistingUserMap($pval->emails);

        if ($exist_emails) {
            $existing_person_id = Arrays::getFirstItem($exist_emails);
            $this->getLogger()->info(sprintf("[%s] Found existing user %d", $log_id, $existing_person_id));
        }

        #------------------------------
        # Create data array
        #------------------------------

        $add_labels = $pval->labels;
        $record = array(
            'name'               => $pval->name ?: '',
            'first_name'         => $pval->first_name ?: '',
            'last_name'          => $pval->last_name ?: '',
            'date_created'       => $pval->date_created ? $pval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            'timezone'           => $pval->timezone ?: 'UTC',
            'is_user'            => 1,
            'is_contact'         => 1,
            'is_confirmed'       => 1,
            'is_agent_confirmed' => 1,
            'secret_string'      => Strings::random(40),
        );

        // Admin/agent flag
        if ($pval->is_agent) {
            $record['is_agent'] = 1;
            if ($pval->is_admin) {
                $record['is_admin'] = 1;
            }
        }

        // Lang
        if ($pval->language) {
            $v = $this->getMappers()->findIdFromMappedValue(MapperInterface::TYPE_LANGUAGE, $pval->language);
            if ($v) {
                $this->getLogger()->info(sprintf("[%s] Found existing language %s", $log_id, $pval->language));
                $record['language_id'] = $v;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s (skipping)", $log_id, $pval->language));
            }
        }

        // Usergroups
        $add_ugs = array();
        if ($pval->usergroups) {
            foreach ($pval->usergroups as $ug) {
                $v = $this->getMappers()->findIdFromMappedValue(MapperInterface::TYPE_USER_GROUP, $ug);
                if ($v) {
                    $this->getLogger()->info(sprintf("[%s] Found existing usergroup %s", $log_id, $ug));
                    $add_ugs[] = $v;
                } else {
                    $this->getLogger()->notice(sprintf("[%s] Could not map usergroup value: %s (skipping)", $log_id, $ug));
                }
            }
        }

        // Org
        if ($pval->organization) {
            $v = $this->getMappers()->findIdFromMappedValue(MapperInterface::TYPE_ORGANIZATION, $pval->organization);
            if ($v) {
                $record['organization_id'] = $v;
            } else {
                $this->getLogger()->info(sprintf("[%s] New organization %s", $log_id, $pval->organization));
                if ($this->isTestMode()) {
                    $record['organization_id'] = -1;
                } else {
                    $this->getDb()->insert('organizations', array(
                        'name'         => $pval->organization,
                        'date_created' => date('Y-m-d H:i:s')
                    ));
                    $record['organization_id'] = $this->getDb()->lastInsertId();
                }

                $this->getMappers()->learnMapping(MapperInterface::TYPE_ORGANIZATION, $record['organization_id']);
            }

            if ($pval->organization_position) {
                $record['organization_position'] = $pval->organization_position;
            }
        }

        // Password
        if ($pval->password && $pval->password_scheme == Person::PASSWORD_SCHEME_PLAIN) {
            $record['password_scheme'] = Person::PASSWORD_SCHEME_BCRYPT;

            $hasher = new \PasswordHash(11, false);
            $record['password'] = $hasher->HashPassword($pval->password);
        }

        // Additional emails
        $add_emails_str = array_diff($pval->emails, array_keys($exist_emails));
        if ($add_emails_str) {
            $this->getLogger()->info(sprintf("[%s] New emails: %s", $log_id, implode(', ', $add_emails_str)));
        }

        #------------------------------
        # Save data
        #------------------------------

        if ($this->isTestMode() === false) {
            $update_rec = array();

            if (isset($existing_person_id)) {
                $exist_id = $this->getMappers()->findIdFromMappedValue(MapperInterface::TYPE_PERSON, $existing_person_id);
                $is_new = false;
                $this->getDb()->update('people', $record, array('id' => $exist_id));
                $this->getLogger()->info(sprintf("[%s] Updated %d", $log_id, $exist_id));
            } else {
                $this->getDb()->insert('people', $record);
                $exist_id = $this->getDb()->lastInsertId();
                $is_new = true;

                $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $exist_id));
            }

            if ($exist_id && $add_emails_str) {
                $first_email_id = null;
                foreach ($add_emails_str as $eml) {
                    list (, $eml_domain) = explode('@', $eml, 2);
                    $this->getDb()->insert('people_emails', array(
                        'person_id'      => $exist_id,
                        'email'          => $eml,
                        'email_domain'   => $eml_domain,
                        'is_validated'   => true,
                        'date_created'   => date('Y-m-d H:i:s'),
                        'date_validated' => date('Y-m-d H:i:s'),
                    ));
                    if ($is_new && !$first_email_id) {
                        $first_email_id = $this->getDb()->lastInsertId();
                    }
                }

                if ($is_new && $first_email_id) {
                    $update_rec['primary_email_id'] = $first_email_id;
                }
            }

            if ($exist_id && $add_ugs) {
                $batch = array_map(function ($ug) use ($exist_id) {
                    return array(
                        'person_id'    => $exist_id,
                        'usergroup_id' => $ug
                    );
                }, $add_ugs);

                $this->getDb()->batchInsert('person2usergroups', $batch, true);
            }

            if ($exist_id && $add_labels) {
                $batch = array_map(function ($l) use ($exist_id) {
                    return array(
                        'person_id' => $exist_id,
                        'label'     => $l
                    );
                }, $add_labels);

                $this->getDb()->batchInsert('labels_people', $batch, true);
            }

            if ($update_rec) {
                $this->getDb()->update('people', $update_rec, array('id' => $exist_id));
            }

            #------------------------------
            # Custom Fields
            #------------------------------
            if ($exist_id && !empty($pval->custom_def)) {
                $custom_field_importer = new CustomDefPersonValueImporter(
                    $this->getMode(),
                    $this->getContainer(),
                    $this->getLogger(),
                    $this->getMappers(),
                    $exist_id
                );

                foreach ($pval->custom_def as $custom_value) {
                    $custom_field_importer->importValue($custom_value);
                }
            }
        }
    }

    /**
     * @param  array $emails
     * @return array
     */
    private function getExistingUserMap(array $emails)
    {
        $qs = implode(',', array_fill(0, count($emails), '?'));
        $ret = $this->getDb()->fetchAll("SELECT email, person_id FROM people_emails WHERE email IN ($qs)", $emails);

        $map = array();
        foreach ($ret as $r) {
            $map[$r['email']] = $r['person_id'];
        }

        return $map;
    }

// Remove?
//    private function customFieldExists($title)
//    {
//        $query = 'SELECT id, handler_class FROM custom_def_people WHERE title = ? AND is_enabled = 1';
//        $result = $this->getDb()->fetchAll($query, array($title));
//
//        if (count($result)) {
//            return $result[0];
//        }
//
//        return $result;
//    }
//
//    private function isCustomFieldTypeSupported($field_type)
//    {
//        return in_array($field_type, $this->supported_custom_field_types);
//    }
}
