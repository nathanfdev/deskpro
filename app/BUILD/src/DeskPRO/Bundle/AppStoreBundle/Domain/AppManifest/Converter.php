<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class Converter
{
    /**
     * @param Domain\AppManifest $manifest
     * @return array|Domain\AppStorage\AccessRule[]
     */
    public function convertToAccessRuleList(Domain\AppManifest $manifest) {
        $stateRules = $manifest->getStorage();
        if (0 === count($stateRules)) {
            return [];
        }

        $accessRuleList = [];
        forEach ($stateRules as $stateRule) {
            $options = new Domain\AppStorage\AccessOptions(
                $stateRule->getPermRead(),
                $stateRule->getPermWrite(),
                $stateRule->getIsBackendOnly()
            );

            $accessRule = new Domain\AppStorage\AccessRule($stateRule->getName(), $options);
            $accessRuleList[] = $accessRule;
        }

        return $accessRuleList;
    }
}
