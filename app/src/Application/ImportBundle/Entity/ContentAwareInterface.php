<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use DateTime;

/**
 * Basic properties on content interface.
 *
 * Interface ContentAwareInterface
 */
interface ContentAwareInterface extends LanguageAwareInterface
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
     * Sets an acceptable URL slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Get an acceptable URL slug.
     *
     * Turns a string into an acceptable URL slug.
     * "My Great Title!" becomes "my-great-title"
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug);

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
     * Total rating.
     *
     * @return int
     */
    public function getTotalRating();

    /**
     * Set total rating.
     *
     * @param int $total_rating
     *
     * @return $this
     */
    public function setTotalRating($total_rating);

    /**
     * Number of comments.
     *
     * @return int
     */
    public function getNumComments();

    /**
     * Set number of comments.
     *
     * @param int $num_comments
     *
     * @return $this
     */
    public function setNumComments($num_comments);

    /**
     * Number of rating.
     *
     * @return int
     */
    public function getNumRatings();

    /**
     * Set number of rating.
     *
     * @param int $num_ratings
     *
     * @return $this
     */
    public function setNumRatings($num_ratings);

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
