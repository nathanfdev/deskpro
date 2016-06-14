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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI;

use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixtureLoader;
use DateTime;

/**
 * Class AbstractFieldsLoader.
 */
abstract class AbstractFieldsLoader extends AbstractFixtureLoader
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @return array
     */
    public function getRandomFields()
    {
        if (null === $this->fields) {
            $this->load();
        }
        if (empty($this->fields)) {
            throw new \RuntimeException('No fields');
        }

        $fields = $this->fields;
        $random = array();

        for ($i = 0; $i < 5; ++$i) {
            if (empty($fields)) {
                break;
            }

            $random = array_merge($random, array_splice($fields, rand(0, count($fields) - 1), 1));
        }

        return $random;
    }

    /**
     * Returns random value by field type.
     *
     * @param array $field
     *
     * @return mixed
     */
    protected function getRandomFieldValue(array $field)
    {
        switch ($field['type']) {
            case 'checkbox':
                return $this->getRandomBool();

            case 'date':
                return $this->getRandomDateTime(new DateTime('-1 year'), new DateTime())->format('c');

            case 'decimal':
                return rand(0, 1000) / 10;

            case 'dropdown':
                $options = $field['custom_field_options'];
                if (!empty($options)) {
                    return $options[rand(0, count($options) - 1)]['value'];
                }

                break;

            case 'integer':
                return rand(0, 100);

            case 'regexp':
                return 'some string';

            case 'text':
                return 'some string';

            case 'textarea':
                return 'some text';
        }

        return;
    }
}
