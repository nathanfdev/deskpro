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

namespace Application\ImportBundle\CsvImport;

use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\ImportBundle\Importer\Importer;
use Application\ImportBundle\Importer\ImporterCollection;
use Application\ImportBundle\Model\Person as PersonModel;
use Application\ImportBundle\Model\PersonCustomDef as PersonCustomDefModel;
use Application\ImportBundle\Parser\Parser;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use Doctrine\ORM\EntityManager;
use DpSys\Features;

/**
 * Class CsvImporter.
 */
class CsvImporter
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Importer
     */
    private $importer;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var Translate
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param Importer             $importer
     * @param Parser               $parser
     * @param Mailer               $mailer
     * @param Translate            $translator
     * @param UserViewModelFactory $viewModelFactory
     * @param EmailSender          $emailSender
     * @param Features             $featureFlags     // Temporary until SendmailBundle is permanently activated
     */
    public function __construct(
        EntityManager $em,
        Importer $importer,
        Parser $parser,
        Mailer $mailer,
        Translate $translator,
        UserViewModelFactory $viewModelFactory,
        EmailSender $emailSender,
        Features $featureFlags
    ) {
        $this->em               = $em;
        $this->importer         = $importer;
        $this->parser           = $parser;
        $this->mailer           = $mailer;
        $this->translator       = $translator;
        $this->viewModelFactory = $viewModelFactory;
        $this->emailSender      = $emailSender;
        $this->featureFlags     = $featureFlags;
    }

    /**
     * Import person entity from CSV data.
     *
     * @param array  $fieldMaps
     * @param array  $data
     * @param string $ref
     * @param bool   $sendWelcomeEmail
     *
     * @return bool|int
     */
    public function importPerson(array $fieldMaps, array $data, $ref, $sendWelcomeEmail = true)
    {
        $customDefs = [];
        $personData = [];

        foreach ($fieldMaps as $columnId => $info) {
            if (empty($info['map']) || !isset($data[$columnId])) {
                continue;
            }

            $mapField    = $info['map'];
            $columnValue = $data[$columnId];

            if ($columnValue === '') {
                continue;
            }

            switch ($mapField) {
                // base fields
                case 'primary_email':
                case 'secondary_email':
                    $personData['emails'][] = $columnValue;
                    break;
                case 'first_name':
                case 'last_name':
                case 'name':
                case 'title_prefix':
                case 'password':
                case 'organization':
                case 'organization_position':
                    $personData[$mapField] = $columnValue;
                    break;
                case 'language':
                    if (is_numeric($columnValue)) {
                        /** @var Language $language */
                        $language = $this->em->find(Language::class, $columnValue);
                        if ($language) {
                            $columnValue = $language->getLangCode();
                        } else {
                            $columnValue = null;
                        }
                    }

                    $personData[$mapField] = $columnValue;
                    break;

                // contact data
                case 'website':
                    $personData['contact_data']['website'][] = [
                        'url' => $columnValue,
                    ];
                    break;
                case 'twitter':
                    $personData['contact_data']['twitter'][] = [
                        'username' => $columnValue,
                    ];
                    break;
                case 'linkedin':
                    $personData['contact_data']['linked_in'][] = [
                        'url' => $columnValue,
                    ];
                    break;
                case 'facebook':
                    $personData['contact_data']['facebook'][] = [
                        'url' => $columnValue,
                    ];
                    break;
                case 'im':
                    if (empty($info['type'])) {
                        $info['type'] = 'aim';
                    }

                    $personData['contact_data']['instant_message'][] = [
                        'service'  => $info['type'],
                        'username' => $columnValue,
                    ];
                    break;
                case 'phone':
                    $personData['contact_data']['phone'][] = [
                        'number' => $columnValue,
                        'type'   => isset($info['type']) ? $info['type'] : 'phone',
                    ];
                    break;
                case 'address':
                case 'address1':
                case 'address2':
                    $label = isset($info['label']) ? $info['label'] : 0;
                    if (isset($personData['contact_data']['address'][$label]['address'])) {
                        $columnValue = $personData['contact_data']['address'][$label]['address']."\n".$columnValue;
                    }

                    $personData['contact_data']['address'][$label]['address'] = $columnValue;
                    break;
                case 'city':
                case 'state':
                case 'zip':
                case 'country':
                    $label = isset($info['label']) ? $info['label'] : 0;

                    $personData['contact_data']['address'][$label][$mapField] = $columnValue;
                    break;
            }

            // custom fields
            $customFieldName = null;
            if (preg_match('/^custom_(\d+)$/', $mapField, $match)) {
                $customField = $this->em->find(CustomDefPerson::class, $match[1]);
                if ($customField) {
                    $customFieldName = $customField->getTitle();
                }
            } elseif ($mapField === 'new_custom') {
                if (!empty($info['title'])) {
                    $customFieldName = $info['title'];
                    $customDefs[]    = [
                        'name'        => $customFieldName,
                        'widget_type' => !empty($info['handler_class']) ? $info['handler_class'] : 'text',
                    ];
                }
            }

            if ($customFieldName) {
                $personData['custom_fields'][] = [
                    'name'  => $customFieldName,
                    'value' => $columnValue,
                ];
            }
        }

        // check if it's a new person
        $isNew = true;
        if (!empty($personData['emails'])) {
            /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
            $personRepo = $this->em->getRepository(Person::class);
            if ($personRepo->findByEmails($personData['emails'])) {
                $isNew = false;
            }
        } else {
            // unable to create a person w/o email
            return false;
        }

        if ($isNew) {
            $personData['labels'][] = 'import-'.$ref;
        }

        $collection  = new ImporterCollection();
        $personOid   = reset($personData['emails']);
        $personModel = $this->parser->exportRawData($personOid, $personData, PersonModel::class);
        if ($personModel) {
            $collection->add($personModel);
        }

        foreach ($customDefs as $customDefData) {
            $defOid   = $customDefData['name'];
            $defModel = $this->parser->exportRawData($defOid, $customDefData, PersonCustomDefModel::class);

            if ($defModel) {
                $collection->add($defModel);
            }
        }

        $this->importer->writeData($collection);

        // get person object
        $people = $personRepo->findByEmails($personData['emails']);
        $person = reset($people);

        if ($sendWelcomeEmail && $isNew && $person) {
            if ($this->featureFlags->hasBeta('email_templates')) {
                $viewModel = $this->viewModelFactory
                    ->createRegisterWelcomeByAgentModel($person->getPlaintextPassword());
                $this->emailSender->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->mailer->createMessage();
                $message->setToPerson($person);
                $message->setTemplate('DeskPRO:emails_user:register-welcome-byagent.html.twig', ['person' => $person]);
                $this->translator->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                    $message->prepare();
                });

                $this->mailer->send($message);
            }
        }

        return $person ? $person->getId() : false;
    }
}
