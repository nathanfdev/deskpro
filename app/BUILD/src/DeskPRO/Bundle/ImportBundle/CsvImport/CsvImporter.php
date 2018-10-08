<?php

namespace DeskPRO\Bundle\ImportBundle\CsvImport;

use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\MailerUtils;
use DeskPRO\Bundle\ImportBundle\Model\Person as PersonModel;
use DeskPRO\Bundle\ImportBundle\Model\PersonCustomDef as PersonCustomDefModel;
use DeskPRO\Bundle\ImportBundle\Parser\Parser;
use DeskPRO\Bundle\ImportBundle\Writer\Importer;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var MailerUtils
     */
    private $mailerUtils;

    /**
     * @var UserViewModelFactory
     */
    private $viewModelFactory;

    /**
     * @var Features
     */
    private $featureFlags;

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
     * @param MailerUtils          $mailerUtils
     * @param Translate            $translator
     * @param UserViewModelFactory $viewModelFactory
     * @param Features             $featureFlags     // Temporary until SendmailBundle is permanently activated
     */
    public function __construct(
        EntityManager        $em,
        Importer             $importer,
        Parser               $parser,
        Mailer               $mailer,
        MailerUtils          $mailerUtils,
        Translate            $translator,
        UserViewModelFactory $viewModelFactory,
        Features             $featureFlags
    ) {
        $this->em               = $em;
        $this->importer         = $importer;
        $this->parser           = $parser;
        $this->mailer           = $mailer;
        $this->mailerUtils      = $mailerUtils;
        $this->translator       = $translator;
        $this->viewModelFactory = $viewModelFactory;
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

        $collection  = new ArrayCollection();
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

        if ($personModel && $personModel->getPassword()) {
            $person->setPassword($personModel->getPassword());
        }

        if ($sendWelcomeEmail && $isNew && $person) {
            if ($this->featureFlags->hasBeta('email_templates')) {
                $viewModel = $this->viewModelFactory
                    ->createRegisterWelcomeByAgentModel($person->getPlaintextPassword());
                $this->mailerUtils->sendModelWithPersonContext($person, $viewModel, ['to' => $person]);
            } else {
                $message = $this->mailer->createMessage();
                $message->setToPerson($person);
                $message->setTemplate('DeskPRO:emails_user:register-welcome-byagent.html.twig', ['person' => $person]);
                $this->mailerUtils->sendWithPersonContext($message, $person);
            }
        }

        return $person ? $person->getId() : false;
    }
}
