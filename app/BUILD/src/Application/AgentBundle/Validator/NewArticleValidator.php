<?php

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;
use Orb\Util\Strings;
use Orb\Validator\AbstractValidator;

class NewArticleValidator extends AbstractValidator
{
    /**
     * @var \DateTime
     */
    private $now;

    /**
     * @var KbSettings
     */
    private $kbSettings;

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $this->now         = new \DateTime();
        $this->kbSettings  = App::$container->get('portal_settings_resolver')->getKbSettings();
    }

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

        if (empty(trim(Strings::stripTags($article->content)))) {
            $this->addError('content.missing');
        }

        if (!$article->status) {
            $this->addError('status.invalid');
        }

        if ($this->kbSettings->isRequireReviewDate()) {
            if (!$article->review_interval_count) {
                $this->addError('review_date.required');
            } else {
                if (!$this->isMinReviewDateValid($article->review_interval_count, $article->review_interval_unit)) {
                    $this->addError('review_date.min');
                }
                if (!$this->isMaxReviewDateValid($article->review_interval_count, $article->review_interval_unit)) {
                    $this->addError('review_date.max');
                }
            }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    /**
     * @param int    $interval
     * @param string $unit
     *
     * @return bool
     */
    public function isMinReviewDateValid($interval, $unit)
    {
        if ($interval < 1) {
            return false;
        }

        $articleReviewDate = $this->getDateTimeFromInterval($interval, $unit ?: Article::REVIEW_DATE_UNIT_DAYS);
        if ($this->kbSettings->isMinReviewDate() && $this->kbSettings->getMinReviewDateInterval()) {
            $minReviewDate = $this->getDateTimeFromInterval($this->kbSettings->getMinReviewDateInterval(), $this->kbSettings->getMinReviewDateUnit() ?: Article::REVIEW_DATE_UNIT_DAYS);

            return $minReviewDate <= $articleReviewDate;
        }

        return true;
    }

    /**
     * @param int    $interval
     * @param string $unit
     *
     * @return bool
     */
    public function isMaxReviewDateValid($interval, $unit)
    {
        $articleReviewDate = $this->getDateTimeFromInterval($interval, $unit ?: Article::REVIEW_DATE_UNIT_DAYS);
        if ($this->kbSettings->isMaxReviewDate() && $this->kbSettings->getMaxReviewDateInterval()) {
            $maxReviewDate = $this->getDateTimeFromInterval($this->kbSettings->getMaxReviewDateInterval(), $this->kbSettings->getMaxReviewDateUnit() ?: Article::REVIEW_DATE_UNIT_DAYS);

            return $maxReviewDate >= $articleReviewDate;
        }

        return true;
    }

    /**
     * @param int    $interval
     * @param string $unit
     *
     * @return \DateTime
     */
    private function getDateTimeFromInterval($interval, $unit)
    {
        $date = clone $this->now;

        if ($interval < 0) {
            $interval = 0;
        }

        return $date->modify("+$interval $unit");
    }
}
