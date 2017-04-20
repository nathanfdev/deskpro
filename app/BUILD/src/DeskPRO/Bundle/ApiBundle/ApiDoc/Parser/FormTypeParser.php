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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

/**
 * Class FormTypeParser.
 */
class FormTypeParser extends \Nelmio\ApiDocBundle\Parser\FormTypeParser
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        return parent::supports($this->prepareOptions($item));
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $item)
    {
        return parent::parse($this->prepareOptions($item));
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function prepareOptions(array $item)
    {
        if (!empty($item['options'])) {
            foreach ($item['options'] as &$option) {
                if (is_string($option) && class_exists($option)) {
                    $reflection = new \ReflectionClass($option);
                    $option     = $reflection->newInstance();
                }
            }
        }

        return $item;
    }
}
