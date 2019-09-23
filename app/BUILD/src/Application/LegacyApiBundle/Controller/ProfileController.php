<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ProfileController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    public function saveInhelpStateAction($id, $state)
    {
        //TODO refactor+test
        $this->db->replace('people_prefs', [
            'person_id'   => $this->person->getId(),
            'name'        => 'inhelp.'.$id,
            'value_str'   => $state,
            'value_array' => 'N;',
        ]);

        return $this->createSuccessResponse();
    }
}
