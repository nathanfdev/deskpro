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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Twilio\Exceptions\TwilioException;

/**
 * Class VoiceAccountListener.
 */
class VoiceAccountListener
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     * @param EntityManager $em
     */
    public function __construct(TwilioAdapter $twilioAdapter, EntityManager $em)
    {
        $this->twilioAdapter = $twilioAdapter;
        $this->em            = $em;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param VoiceAccount $account
     *
     * @throws TwilioException
     */
    public function onCreate(VoiceAccount $account)
    {
        $workspace = $this->twilioAdapter->createWorkspace($account);
        if (!$workspace) {
            throw new TwilioException('Unable to create Twilio workspace');
        }

        $account->setWorkspaceSid($workspace->sid);
    }

    /**
     * @ORM\PreRemove()
     *
     * @param VoiceAccount $account
     */
    public function onRemove(VoiceAccount $account)
    {
        // delete related workspace from Twilio
        $this->twilioAdapter->deleteWorkspace($account);
        $account->setWorkspaceSid(null);

        // disable agent voice flags cause all their workers were deleted as well
        $qb = $this->em->createQueryBuilder();
        $qb
            ->update(AgentData::class, 'a')
            ->set('a.isVoiceEnabled', 0)
            ->set('a.voiceWorkerSid', 'NULL')
            ->getQuery()
            ->execute()
        ;

        // clear voice targets
        $this->em->getConnection()->executeQuery('DELETE FROM voice_targets');
    }
}
