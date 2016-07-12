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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData\Inline;

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Entity\ContactData;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserHelper;

/**
 * Inline contact data parser.
 *
 * Class InlineContactData
 */
class InlineContactData extends AbstractParserHelper
{
    /**
     * @var ContactType\ContactTypeInterface[]
     */
    private $contact_types;

    /**
     * Constructor.
     *
     * @param ContactType\Collection $contact_types
     */
    public function __construct(ContactType\Collection $contact_types)
    {
        $this->contact_types = $contact_types;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return 'inline_'.Entity\EntityInterface::TYPE_CONTACT_DATA;
    }

    /**
     * Parses inline contact data from entity.
     *
     * @param array  $data
     * @param string $destination
     *
     * @return ContactData[]
     */
    public function export(array $data, $destination)
    {
        $contact_data = [];
        foreach ($this->contact_types as $num => $contact_type) {
            $value = $contact_type->getValue($data);
            if (!$value) {
                continue;
            }

            $handler = ContactDataFactory::getHandler($contact_type->getContactType());
            if (!method_exists($handler, $contact_type->getMethod())) {
                throw new \RuntimeException(sprintf(
                    'Contact type `%s` has no method `%s`',
                    $contact_type->getContactType(), $contact_type->getMethod()
                ));
            }

            $contact = $handler->{$contact_type->getMethod()}($value);
            if ($contact instanceof ContactData) {
                $contact
                    ->setOid($num)
                    ->setDestination($destination)
                ;

                $contact_data[] = $contact;
            }
        }

        return $contact_data;
    }
}
