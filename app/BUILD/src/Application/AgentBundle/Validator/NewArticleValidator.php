<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Validator\AbstractValidator;

class NewArticleValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewArticle $article
     *
     * @return bool
     */
    protected function checkIsValid($article)
    {
        if (!$article->category_id) {
            $this->addError('category_id.invalid');
        }

        $cat = null;
        if ($article->category_id) {
            $cat = App::getOrm()->find('DeskPRO:ArticleCategory', $article->category_id);
        }
        if (!$cat) {
            $this->addError('category_id.invalid');
        }

        if (!$article->title) {
            $this->addError('title.missing');
        }

        if (!$article->status) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
