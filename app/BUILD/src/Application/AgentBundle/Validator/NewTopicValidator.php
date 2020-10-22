<?php



namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use Orb\Validator\AbstractValidator;

class NewTopicValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewTopic $topic
     *
     * @return bool
     */
    protected function checkIsValid($topic)
    {
        if (!$topic->guide_id) {
            $this->addError('guide_id.invalid');
        } else {
            $cat = App::getOrm()->find(Guide::class, $topic->guide_id);
            if (!$cat) {
                $this->addError('guide_id.invalid');
            }
        }

        if (!$topic->title) {
            $this->addError('title.missing');
        }

        if (!$topic->status) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        switch ($topic->type) {
            case 'chapter':
                if ($cat->isUseVolumes()) {
                    if (!$topic->parent_id) {
                        $this->addError('parent.missing');
                    } else {
                        $parent = App::getOrm()->find(Topic::class, $topic->parent_id);
                        if ($parent->getParent()) {
                            $this->addError('parent.must_be_volume');
                        }
                    }
                }

                break;
            case 'page':
                if (!$topic->parent_id) {
                    $this->addError('parent.missing');
                } else {
                    $parent = App::getOrm()->find(Topic::class, $topic->parent_id);
                    if ($cat->isUseVolumes()) {
                        if (!$parent->getParent()) {
                            $this->addError('parent.cannot_be_volume');
                        }
                    }
                }

                break;
            default:
                if ($topic->parent_id) {
                    $this->addError('parent.no_parent');
                }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
