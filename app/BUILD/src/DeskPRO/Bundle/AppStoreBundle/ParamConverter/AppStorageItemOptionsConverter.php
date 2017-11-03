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

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppStorageItemOptionsConverter implements ParamConverterInterface
{
    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $options = [];

        $optionKeyToQueryParam = [
            'mode' => 'options.mode'
        ];
        foreach ($optionKeyToQueryParam as $optionKey => $optionParamName) {
            //see  http://ca.php.net/variables.external
            // php replaces dots with underscores in query parameters, very funny indeed
            $phpSafeParamName = str_replace('.', '_', $optionParamName);
            if ($request->query->has($phpSafeParamName)) {
                $options[$optionKey] = $request->query->get($phpSafeParamName);
            }
        }

        $request->attributes->set('options', $options);
        return true;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        $class = $configuration->getClass();
        return empty($class);
    }
}
