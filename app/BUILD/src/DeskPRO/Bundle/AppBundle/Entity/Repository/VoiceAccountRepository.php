<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use Doctrine\ORM\EntityRepository;

/**
 * Class VoiceAccountRepository.
 */
class VoiceAccountRepository extends EntityRepository
{
    /**
     * @return VoiceAccount|null
     */
    public function getVoiceAccount()
    {
        return $this->findOneBy([], ['id' => 'asc']);
    }
}
