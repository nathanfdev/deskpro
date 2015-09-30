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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper;

/**
 * Generator mapper interface.
 *
 * Interface MapperInterface
 */
interface MapperInterface
{
    const TYPE_PERSON                  = 'person';
    const TYPE_PERSON_EMAIL            = 'person_email';
    const TYPE_PERSON_LABEL            = 'person_label';
    const TYPE_TICKET_DEPARTMENT       = 'ticket_department';
    const TYPE_TICKET                  = 'ticket';
    const TYPE_TICKET_CATEGORY         = 'ticket_category';
    const TYPE_TICKET_LABEL            = 'ticket_label';
    const TYPE_TICKET_LAYOUT           = 'ticket_layout';
    const TYPE_TICKET_MESSAGE          = 'ticket_message';
    const TYPE_TICKET_WORKFLOW         = 'ticket_workflow';
    const TYPE_TICKET_PRIORITY         = 'ticket_priority';
    const TYPE_DEPARTMENT              = 'department';
    const TYPE_PRODUCT                 = 'product';
    const TYPE_USER_GROUP              = 'usergroup';
    const TYPE_ORGANIZATION            = 'organization';
    const TYPE_ORGANIZATION_LABEL      = 'organization_label';
    const TYPE_LANGUAGE                = 'language';
    const TYPE_ARTICLE_CATEGORY        = 'article_category';
    const TYPE_ARTICLE_COMMENT         = 'article_comment';
    const TYPE_ARTICLE_LABEL           = 'article_label';
    const TYPE_ARTICLE                 = 'article';
    const TYPE_NEWS_CATEGORY           = 'news_category';
    const TYPE_NEWS_LABEL              = 'news_label';
    const TYPE_NEWS                    = 'news';
    const TYPE_FEEDBACK_CATEGORY       = 'feedback_category';
    const TYPE_FEEDBACK_LABEL          = 'feedback_label';
    const TYPE_FEEDBACK                = 'feedback';
    const TYPE_DOWNLOAD_CATEGORY       = 'download_category';
    const TYPE_DOWNLOAD_LABEL          = 'download_label';
    const TYPE_DOWNLOAD                = 'download';
    const TYPE_CUSTOM_DEF_TICKET       = 'custom_def_ticket';
    const TYPE_CUSTOM_DEF_PERSON       = 'custom_def_people';
    const TYPE_CUSTOM_DEF_FEEDBACK     = 'custom_def_feedback';
    const TYPE_CUSTOM_DEF_ORGANIZATION = 'custom_def_organization';
    const TYPE_CUSTOM_DEF_ARTICLE      = 'custom_def_article';
    const TYPE_BLOB_DATA               = 'blob_data';
    const TYPE_EMAIL_ACCOUNT           = 'email_account';
    const TYPE_OBJECT_LANG             = 'object_lang';
    const TYPE_IMPORT_MAP              = 'import_map';

    /**
     * Returns DeskPRO record type.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the DeskPRO record by criteria.
     *
     * @param array $criteria
     * @param bool  $throw_exception
     *
     * @return mixed
     */
    public function findOneBy(array $criteria, $throw_exception = true);
}
