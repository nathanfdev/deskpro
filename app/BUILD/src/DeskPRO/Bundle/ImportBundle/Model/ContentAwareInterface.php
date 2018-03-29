<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use DateTime;

/**
 * Basic properties on content interface.
 *
 * Interface ContentAwareInterface
 */
interface ContentAwareInterface extends LanguageAwareInterface, PrimaryImportModelInterface
{
    /**
     * Entity title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set entity title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * Entity content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Set entity content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content);

    /**
     * Set status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * View count.
     *
     * @return int
     */
    public function getViewCount();

    /**
     * Set a view count.
     *
     * @param int $view_count
     *
     * @return $this
     */
    public function setViewCount($view_count);

    /**
     * Date created.
     *
     * @return DateTime
     */
    public function getDateCreated();

    /**
     * Set date created.
     *
     * @param DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(DateTime $date_created);

    /**
     * Date published.
     *
     * @return DateTime
     */
    public function getDatePublished();

    /**
     * Set date published.
     *
     * @param DateTime $date_published
     *
     * @return $this
     */
    public function setDatePublished(DateTime $date_published);
}
