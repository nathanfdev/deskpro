<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Validator\AbstractValidator;

class NewDownloadValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewDownload $download
     *
     * @return bool
     */
    protected function checkIsValid($download)
    {
        if (!$download->category_id) {
            $this->addError('category_id.invalid');
        } else {
            $cat = App::getOrm()->find('DeskPRO:DownloadCategory', $download->category_id);
            if (!$cat) {
                $this->addError('category_id.invalid');
            }
        }

        if (!$download->status) {
            $this->addError('status.invalid');
        }

        if (!$download->attach && !$download->fileurl) {
            $this->addError('attach.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
