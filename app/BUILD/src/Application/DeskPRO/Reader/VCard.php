<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reader;

class VCard extends \File_IMC
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function applyToPerson($content, \Application\DeskPRO\Entity\Person $person)
    {
        $fields = self::parseVCard($content);

        //var_dump($fields); die;

        if (isset($fields['name'])) {
            if (isset($fields['name']['firstname'])) {
                $person['first_name'] = $fields['name']['firstname'];
            }

            if (isset($fields['name']['lastname'])) {
                $person['last_name'] = $fields['name']['lastname'];
            }

            unset($fields['name']);
        }

        if (isset($fields['emails']) && is_array($fields['emails'])) {
            foreach ($fields['emails'] as $email) {
                $emailExists = \Application\DeskPRO\Entity\PersonEmail::getRepository()->findOneBy([
                    'email' => $email,
                ]);

                if ($emailExists) {
                    continue;
                }

                $newEmail = new \Application\DeskPRO\Entity\PersonEmail();

                $newEmail->email = $email;

                $newEmail->is_validated = true;

                $person->addEmailAddress($newEmail);
            }
            unset($fields['emails']);
        }

        if (isset($fields['organization'])) {
            $organization = $this->_lookupOrganizationByName($fields['organization']);

            if ($organization) {
                $person['organization'] = $organization;
            }
            unset($fields['organization']);
        }

        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            foreach ($value as $contactData) {
                $contact_data = new \Application\DeskPRO\Entity\PersonContactData();

                $contact_data->person = $person;

                $contact_data->contact_type = $key;

                $contact_data->applyFormData($contactData);

                if ($this->_checkMatchingContactAccounts($contact_data, $person)) {
                    continue;
                }

                $this->em->persist($contact_data);
            }
        }
        $this->em->flush();
    }

    public static function parseVCard($content)
    {
        $parse = self::parse('vCard');

        $vcard = $parse->fromText($content);

        $fields = [];

        if (isset($vcard['VCARD'])) {
            //print_r($vcard['VCARD']); die;
            foreach ($vcard['VCARD'] as $vc) {
                if (isset($vc['EMAIL'])) {
                    foreach ($vc['EMAIL'] as $email) {
                        if (isset($email['value'])) {
                            $fields['emails'][] = $email['value'][0][0];
                        }
                    }
                }

                if (isset($vc['N'])) {
                    $fields['name']['firstname'] = @$vc['N'][0]['value'][1][0];
                    $fields['name']['lastname']  = @$vc['N'][0]['value'][0][0];
                }

//                if(isset($vc['FN'])
//                && isset($vc['FN'][0]['value'])) {
//                    $fields['name']['fullname'] = $vc['FN'][0]['value'][0][0];
//                }

                if (isset($vc['IMPP'])) {
                    //print_r($vc['IMPP']); die;
                    $fields['instant_message'] = [];
                    foreach ($vc['IMPP'] as $IM) {
                        if (isset($IM['value'])) {
                            $iMFields = explode(':', $IM['value'][0][0]);

                            if (isset($IM['param']['X-SERVICE-TYPE'][0]) && $IM['param']['X-SERVICE-TYPE'][0] == 'GoogleTalk') {
                                $fields['instant_message'][] = [
                                    'username' => $iMFields[1],
                                    'service'  => 'gtalk',
                                    'comment'  => @$IM['param']['TYPE'][0],
                                ];
                            } elseif (isset($IM['param']['X-SERVICE-TYPE'][0])) {
                                $fields['instant_message'][] = [
                                    'username' => $iMFields[1],
                                    'service'  => strtolower($IM['param']['X-SERVICE-TYPE'][0]),
                                    'comment'  => @$IM['param']['TYPE'][0],
                                ];
                            } else {
                                $fields['instant_message'][] = [
                                    'username' => $iMFields[1],
                                    'service'  => $iMFields[0],
                                ];
                            }
                        }
                    }
                }

                if (isset($vc['TEL'])) {
                    //print_r($vc['TEL']); die;
                    $fields['phone'] = [];
                    foreach ($vc['TEL'] as $TEL) {
                        if (isset($TEL['value'][0][0])) {
                            $countryCode = null;

                            $type = @$TEL['param']['TYPE'][1];

                            $comment = @$TEL['param']['TYPE'][0];

                            if ($TEL['value'][0][0][0] === '+') {
                                // International number with a plus sign and a country code
                                $countryCode = substr($TEL['value'][0][0], 1, 2);

                                $phoneNumber = substr($TEL['value'][0][0], 3);
                            } elseif (strlen($TEL['value'][0][0]) >= 12) {
                                // International number without a plus sign
                                $countryCode = substr($TEL['value'][0][0], 0, 2);

                                $phoneNumber = substr($TEL['value'][0][0], 2);
                            } else {
                                $phoneNumber = $TEL['value'][0][0];
                            }

                            $fields['phone'][] = [
                                'comment'              => $comment,
                                'country_calling_code' => $countryCode,
                                'number'               => $phoneNumber,
                                'type'                 => $type,
                            ];
                        }
                    }
                }

                if (isset($vc['URL']) && is_array($vc['URL'])) {
                    $fields['website'] = [];

                    foreach ($vc['URL'] as $Url) {
                        if (isset($Url['value'][0][0])) {
                            $fields['website'][] = [
                                'url' => $Url['value'][0][0],
                            ];
                        }
                    }
                }

                if (isset($vc['ORG'][0]['value'][0][0])) {
                    $fields['organization'] = $vc['ORG'][0]['value'][0][0];
                }
            }
        }

        return $fields;
    }

    protected function _lookupOrganizationByName($name)
    {
        return \Application\DeskPRO\Entity\Organization::getRepository()->findOneBy([
            'name' => $name,
        ]);
    }

    protected function _checkMatchingContactAccounts(\Application\DeskPRO\Entity\PersonContactData $contactData, \Application\DeskPRO\Entity\Person $person)
    {
        $existingContactDatas = $person->contact_data;

        $type = $contactData->contact_type;

        switch ($type) {
            case 'instant_message':
                foreach ($existingContactDatas as $existingContactData) {
                    if ($existingContactData->field_1 === $contactData->field_1 &&
                            $existingContactData->field_2 === $contactData->field_2) {
                        return true;
                    }
                }
                break;

            case 'phone':
                foreach ($existingContactDatas as $existingContactData) {
                    if ($existingContactData->field_1 === $contactData->field_1 &&
                            $existingContactData->field_2 === $contactData->field_2) {
                        return true;
                    }
                }
                break;

            case 'facebook':
                foreach ($existingContactDatas as $existingContactData) {
                    if ($existingContactData->field_1 === $contactData->field_1) {
                        return true;
                    }
                }
                break;

            case 'website':
                foreach ($existingContactDatas as $existingContactData) {
                    if ($existingContactData->field_1 === $contactData->field_1) {
                        return true;
                    }
                }
                break;

            default:
                break;
        }
    }
}
