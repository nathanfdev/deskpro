<?php

namespace DeskPRO\Bundle\ImportBundle\CsvImport;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\MailerUtils;
use DeskPRO\Bundle\ImportBundle\Model\Organization as OrganizationModel;
use DeskPRO\Bundle\ImportBundle\Model\OrganizationCustomDef as OrganizationCustomDefModel;
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
        EntityManager $em,
        Importer $importer,
        Parser $parser,
        Mailer $mailer,
        MailerUtils $mailerUtils,
        Translate $translator,
        UserViewModelFactory $viewModelFactory,
        Features $featureFlags
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
        $personCustomDefs = [];
        $orgCustomDefs    = [];
        $personData       = [];
        $orgData          = [];
        $brandName        = null;

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
                case 'organization_position':
                    $personData[$mapField] = $columnValue;

                    break;
                case 'organization':
                    $orgData['name']       = $columnValue;
                    $personData[$mapField] = $columnValue;

                    break;
                case 'language':
                    if (is_numeric($columnValue)) {
                        /** @var Language $language */
                        $language = $this->em->find(Language::class, $columnValue);
                        if ($language) {
                            $columnValue = $language->getLocale();
                        } else {
                            $columnValue = null;
                        }
                    }

                    $personData[$mapField] = $columnValue;

                    break;
                case 'brand':
                    $brandName = $columnValue;

                    if (is_numeric($columnValue)) {
                        /** @var Brand $brand */
                        $brand = $this->em->find(Brand::class, $columnValue);

                        if ($brand) {
                            $brandName = $brand->getName();
                        } else {
                            $brandName = null;
                        }
                    }

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
            $addCustomData = function (array &$customDefs, &$objectData, $prefix = '') use ($mapField, $info, $columnValue) {
                $customFieldName = null;
                if (preg_match('/^'.$prefix.'custom_(\d+)$/', $mapField, $match)) {
                    $entityClass = $prefix === 'org_' ? CustomDefOrganization::class : CustomDefPerson::class;
                    $customField = $this->em->find($entityClass, $match[1]);
                    if ($customField) {
                        $customFieldName = $customField->getTitle();
                    }
                } elseif ($mapField === $prefix.'new_custom') {
                    if (!empty($info['title'])) {
                        $customFieldName = $info['title'];
                        $customDefs[]    = [
                            'title'       => $customFieldName,
                            'widget_type' => !empty($info['handler_class']) ? $info['handler_class'] : 'text',
                        ];
                    }
                }

                if ($customFieldName) {
                    $objectData['custom_fields'][] = [
                        'name'  => $customFieldName,
                        'value' => $columnValue,
                    ];
                }
            };

            $addCustomData($personCustomDefs, $personData);
            $addCustomData($orgCustomDefs, $orgData, 'org_');
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

        $collection = new ArrayCollection();

        // persist org data
        foreach ($orgCustomDefs as $customDefData) {
            $defOid   = $customDefData['title'];
            $defModel = $this->parser->parseRawData($defOid, $customDefData, OrganizationCustomDefModel::class);

            if ($defModel) {
                $collection->add($defModel);
            }
        }

        if (isset($orgData['name'])) {
            $orgOid   = $orgData['name'];
            $orgModel = $this->parser->parseRawData($orgOid, $orgData, OrganizationModel::class);
            if ($orgModel) {
                $collection->add($orgModel);
            }
        }

        // persist person data
        foreach ($personCustomDefs as $customDefData) {
            $defOid   = $customDefData['title'];
            $defModel = $this->parser->parseRawData($defOid, $customDefData, PersonCustomDefModel::class);

            if ($defModel) {
                $collection->add($defModel);
            }
        }

        $personOid   = reset($personData['emails']);
        $personModel = $this->parser->parseRawData($personOid, $personData, PersonModel::class);
        if ($personModel) {
            $collection->add($personModel);
        }

        $this->importer->writeData($collection, $brandName);

        // get person object
        $people = $personRepo->findByEmails($personData['emails']);
        $person = reset($people);

        if ($person && $sendWelcomeEmail && $isNew) {
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
