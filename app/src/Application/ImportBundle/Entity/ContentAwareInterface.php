<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

/**
 * Basic properties on content interface
 *
 * Interface ContentAwareInterface
 * @package Application\ImportBundle\Entity
 */
interface ContentAwareInterface
{
    /**
     * Entity title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set entity title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Entity content
     *
     * @return string
     */
    public function getContent();

    /**
     * Set entity content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Sets an acceptable URL slug
     *
     * @return string
     */
    public function getSlug();

    /**
     * Get an acceptable URL slug
     *
     * Turns a string into an acceptable URL slug.
     * "My Great Title!" becomes "my-great-title"
     *
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug);

    /**
     * Entity language
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Set entity language
     *
     * @param string $language
     * @return $this
     */
    public function setLanguage($language);
}
