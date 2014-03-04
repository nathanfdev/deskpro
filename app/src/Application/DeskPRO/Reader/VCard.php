<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Reader;

class VCard extends \File_IMC
{
    public function applyToPerson($content, \Application\DeskPRO\Entity\Person $person)
    {
        $content = $this->container->getBlobStorage()->copyBlobRecordToString($blob);

        $fields = self::parseVCard($content);

        //var_dump($fields); die;

        if (isset($fields['name'])) {
            $person['name'] = $fields['name'];
            unset($fields['name']);
        }

        if (isset($fields['emails']) && is_array($fields['emails'])) {
            foreach ($fields['emails'] as $email) {
                // @todo Dupe checking
                $person->emails->add($email);
            }
            unset($fields['emails']);
        }
        
        foreach ($fields as $key => $value) {
            foreach ($value as $contactData) {
                $contact_data = new PersonContactData();
                
                $contact_data->person = $person;

                $contact_data->contact_type = $key;

                $contact_data->applyFormData($contactData);

                $this->em->persist($contact_data);
            }
        }
    }
    
    static public function parseVCard($content)
    {
        $parse = self::parse('vCard');
        
        $vcard = $parse->fromText($content);
        
        $fields = array();

        if(isset($vcard['VCARD'])) {
            //print_r($vcard['VCARD']); die;
            foreach($vcard['VCARD'] as $vc) {

                if(isset($vc['EMAIL'])) {
                    foreach ($vc['EMAIL'] as $email) {
                        if (isset($email['value'])) {
                            $fields['emails'][] = $email['value'][0][0];
                        }
                    }
                }

                if(isset($vc['FN'])
                && isset($vc['FN'][0]['value'])) {
                    $fields['name'] = $vc['FN'][0]['value'][0][0];
                }
                
                if(isset($vc['IMPP'])) {
                    //print_r($vc['IMPP']); die;
                    $fields['instant_message'] = array();
                    foreach ($vc['IMPP'] as $IM) {
                        if (isset($IM['value'])) {
                            
                            $iMFields = explode(":", $IM['value'][0][0]);
                            
                            if (isset($IM['param']['X-SERVICE-TYPE'][0]) && $IM['param']['X-SERVICE-TYPE'][0] == 'GoogleTalk') {
                                $fields['instant_message'][] = array(
                                    'username'  => $iMFields[1],
                                    'service'   => 'gtalk',
                                    'comment'   => @$IM['param']['TYPE'][0]
                                );
                            } elseif(isset ($IM['param']['X-SERVICE-TYPE'][0])) {
                                $fields['instant_message'][] = array(
                                    'username'  => $iMFields[1],
                                    'service'   => strtolower($IM['param']['X-SERVICE-TYPE'][0]),
                                    'comment'   => @$IM['param']['TYPE'][0]
                                );
                            } else {
                                $fields['instant_message'][] = array(
                                    'username'  => $iMFields[1],
                                    'service'   => $iMFields[0]
                                );
                            }
                        }
                    }
                }
                
                if (isset($vc['TEL'])) {
                    //print_r($vc['TEL']); die;
                    $fields['phone'] = array();
                    foreach ($vc['TEL'] as $TEL) {
                        if (isset($TEL['value'][0][0])) {
                            $TEL['value'][0][0] = '9007728285';
                            
                            $countryCode    = null;
                            
                            $type           = @$TEL['param']['TYPE'][1];
                            
                            $comment        = @$TEL['param']['TYPE'][0];
                            
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
                            
                            $fields['phone'][] = array(
                                'comment'               => $comment,
                                'country_calling_code'  => $countryCode,
                                'number'                => $phoneNumber,
                                'type'                  => $type
                            );
                        }
                    }
                }
            }
        }
        
        return $fields;
    }
}