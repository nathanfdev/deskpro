<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * Exporting entity interface.
 *
 * Interface EntityInterface
 */
interface EntityInterface
{
    const TYPE_PERSON                  = 'person';
    const TYPE_PERSON_CUSTOM_DEF       = 'person_custom_def';
    const TYPE_TICKET                  = 'ticket';
    const TYPE_TICKET_MESSAGE          = 'ticket_message';
    const TYPE_TICKET_PRIORITY         = 'ticket_priority';
    const TYPE_TICKET_CUSTOM_DEF       = 'ticket_custom_def';
    const TYPE_BLOB                    = 'blob';
    const TYPE_ATTACHMENT              = 'attachment';
    const TYPE_CUSTOM_FIELD            = 'custom_field';
    const TYPE_DOWNLOAD                = 'download';
    const TYPE_NEWS                    = 'news';
    const TYPE_ARTICLE                 = 'article';
    const TYPE_ARTICLE_CATEGORY        = 'article_category';
    const TYPE_ARTICLE_COMMENT         = 'article_comment';
    const TYPE_ARTICLE_CUSTOM_DEF      = 'article_custom_def';
    const TYPE_FEEDBACK                = 'feedback';
    const TYPE_FEEDBACK_CUSTOM_DEF     = 'feedback_custom_def';
    const TYPE_ORGANIZATION            = 'organization';
    const TYPE_ORGANIZATION_CUSTOM_DEF = 'organization_custom_def';
    const TYPE_CONTACT_DATA            = 'contact_data';
    const TYPE_OBJECT_LANG             = 'object_lang';

    /**
     * Returns raw data.
     *
     * @return array
     */
    public function getRawData();

    /**
     * Set raw data.
     *
     * @param array $raw_data
     */
    public function setRawData($raw_data);

    /**
     * Returns import map key
     * Uses to save mapping between legacy and new entities.
     *
     * @return string
     */
    public function getImportMapKey();

    /**
     * Set import map key.
     *
     * @param string $import_map_key
     *
     * @return $this
     */
    public function setImportMapKey($import_map_key);

    /**
     * Get entity type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get entity oid.
     *
     * @return int|string
     */
    public function getOid();

    /**
     * Get entity destination.
     * It could be a file name or db name.
     *
     * @return string
     */
    public function getDestination();

    /**
     * Get entity prefix.
     *
     * @return string
     */
    public function getDestinationPrefix();

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray();
}
