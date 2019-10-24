<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Validator\AbstractValidator;

class NewCommunityTopicValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewCommunityTopic $communityTopic
     *
     * @return bool
     */
    protected function checkIsValid($communityTopic)
    {
        if (!$communityTopic->forum_id) {
            $this->addError('forum_id.invalid');
        }

        $cat = App::getOrm()->find('DeskPRO:CommunityForum', $communityTopic->forum_id);
        if (!$cat) {
            $this->addError('forum_id.invalid');
        }

        if (!$communityTopic->title) {
            $this->addError('title.missing');
        }

        if (empty(trim(Strings::stripTags($communityTopic->content)))) {
            $this->addError('content.missing');
        }

        if (!$communityTopic->status_code) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
