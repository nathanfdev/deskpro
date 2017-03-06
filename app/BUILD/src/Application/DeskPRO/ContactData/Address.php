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
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class Address extends AbstractContactData
{
    /**
     * Apply form data to a contact record.
     *
     * @param array               $input
     * @param ContactDataAbstract $contact_record
     */
    public function applyFormData(array $input, ContactDataAbstract $contact_record)
    {
        $contact_record
            ->setComment(isset($input['comment']) ? $input['comment'] : '')
            ->setField1(isset($input['address']) ? $input['address'] : '')
            ->setField2(isset($input['city']) ? $input['city'] : '')
            ->setField3(isset($input['state']) ? $input['state'] : '')
            ->setField4(isset($input['zip']) ? $input['zip'] : '')
            ->setField5(isset($input['country']) ? $input['country'] : '');

        $fullAddress = '';
        foreach (range(1, 5) as $i) {
            $method = 'getField'.$i;
            $fullAddress .= ' '.$contact_record->$method();
        }
        // Searchable value without punctuation etc
        $contact_record->setField10(preg_replace('#[^0-9a-zA-Z\s]#', '', $fullAddress));
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @param ContactDataAbstract $contact_record
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        $address_txt = $this->getTextAddress($contact_record);
        $vars        = $this->getApiVars($contact_record);

        return array_merge(
            $vars,
            [
                'comment'      => $contact_record->getComment(),
                'address_html' => nl2br(htmlentities($address_txt)),
                'map_url'      => $this->generateGoogleStaticMapUrl($address_txt),
            ]
        );
    }

    /**
     * Return an array of values that are useful to the API.
     *
     * @param ContactDataAbstract $contact_record
     *
     * @return array
     */
    public function getApiVars(ContactDataAbstract $contact_record)
    {
        return [
            'address' => $contact_record->getField1(),
            'city'    => $contact_record->getField2(),
            'state'   => $contact_record->getField3(),
            'zip'     => $contact_record->getField4(),
            'country' => $contact_record->getField5(),
        ];
    }

    /**
     * @param ContactDataAbstract $contact_record
     *
     * @return string
     */
    private function getTextAddress(ContactDataAbstract $contact_record)
    {
        $segs = [
            str_replace("\n", ', ', Strings::standardEol($contact_record->getField1())),
            implode(
                ', ',
                Arrays::removeFalsey(
                    [
                        $contact_record->getField2(),
                        $contact_record->getField3(),
                        $contact_record->getField4(),
                    ]
                )
            ),
            $contact_record->getField5(),
        ];
        $address_txt = Arrays::removeFalsey($segs);

        return implode("\n", $address_txt);
    }

    /**
     * @param string $address_txt
     *
     * @return string
     */
    private function generateGoogleStaticMapUrl($address_txt)
    {
        $params = [
            'sensor' => 'false',
            'size'   => '200x200',
            'center' => str_replace("\n", ' ', Strings::standardEol($address_txt)),
        ];

        return 'https://maps.googleapis.com/maps/api/staticmap?'.http_build_query($params, null, '&amp;');
    }
}
