<?php

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
