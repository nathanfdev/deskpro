<?php

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
