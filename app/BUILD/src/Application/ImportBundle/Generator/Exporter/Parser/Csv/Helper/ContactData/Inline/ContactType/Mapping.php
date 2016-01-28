<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData\Inline\ContactType;

/**
 * Mapping contact data configuration
 * If we need more that one field to set contact data property (e.g. address) or if we can use "toEntity" method.
 *
 * Class Mapping
 */
class Mapping extends AbstractContactType
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * Constructor.
     *
     * @param string $contact_type
     * @param array  $mapping
     * @param string $method
     */
    public function __construct($contact_type, array $mapping, $method = 'toEntity')
    {
        parent::__construct($contact_type, $method);
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(array $entity)
    {
        $value = array();

        foreach ($this->mapping as $property => $original_property) {
            if (array_key_exists($original_property, $entity)) {
                $value[$property] = $entity[$original_property];
            }
        }

        return !empty($value) ? $value : null;
    }
}
