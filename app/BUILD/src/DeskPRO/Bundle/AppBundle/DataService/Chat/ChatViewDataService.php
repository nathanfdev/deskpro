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

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;
use DeskPRO\Bundle\AppBundle\DataService\CustomFieldUtil;
use Doctrine\ORM\EntityManager;

/**
 * Class ChatViewDataService.
 */
class ChatViewDataService extends AbstractDataService
{
    /**
     * @var CustomFieldUtil
     */
    private $customFieldUtil;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param CustomFieldUtil $customFieldUtil
     */
    public function __construct(EntityManager $em, CustomFieldUtil $customFieldUtil)
    {
        parent::__construct($em);

        $this->customFieldUtil = $customFieldUtil;
    }

    /**
     * @param ChatConversation $chat
     *
     * @return array
     */
    public function getCustomDataForChat(ChatConversation $chat)
    {
        $view = [];

        foreach ($this->getEnabledUserChatFields() as $chatDef) {
            if ($data = $chat->getCustomDataForField($chatDef)) {
                $val = $this->customFieldUtil->getValueForCustomFormField($chatDef, $data);
            } else {
                $val = '';
            }

            $view[] = [
                'label' => $chatDef->getTitle(),
                'value' => $val,
            ];
        }

        return $view;
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefChat[]
     */
    protected function getEnabledUserChatFields()
    {
        /** @var \Application\DeskPRO\EntityRepository\CustomDefChat $repo */
        $repo = $this->em->getRepository(CustomDefChat::class);

        return $repo->getEnabledUserFields();
    }
}
