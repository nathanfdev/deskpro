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

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\AbstractReader;
use Application\ImportBundle\Reader\ZenDesk\Request\Request;
use Application\ImportBundle\Reader\ZenDesk\Request\RequestAdapterInterface;
use DateTime;

/**
 * ZenDesk reader.
 *
 * @see https://developer.zendesk.com/rest_api/docs/core/introduction
 * @see https://developer.zendesk.com/rest_api/docs/core/incremental_export
 * @see https://support.zendesk.com/hc/en-us/articles/204232743
 *
 * Class ZenDeskReader
 *
 * @property ZenDeskConfig $config
 */
class ZenDeskReader extends AbstractReader implements ZenDeskReaderInterface
{
    /**
     * @var RequestAdapterInterface
     */
    private $adapter;

    /**
     * Constructor.
     *
     * @param RequestAdapterInterface $adapter
     * @param ZenDeskConfig           $config
     */
    public function __construct(RequestAdapterInterface $adapter, ZenDeskConfig $config)
    {
        parent::__construct($config);
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $this->getSettings();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings()
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Settings', 'findAll'));

        return $this->toArray($result->settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleCount(DateTime $start_time = null)
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Person', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $result ? $result->count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeople(DateTime $start_time = null)
    {
        $people = array();
        $result = $this->adapter->doRequest(Request::createCoreAPI('Person', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        if (is_array($result->users)) {
            foreach ($result->users as $person) {
                $people[] = $this->toArray($person);
            }
        }

        return $people;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleEndTime(DateTime $start_time = null)
    {
        $request = $this->adapter->doRequest(Request::createCoreAPI('Person', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $this->getIncrementalEndDateTime($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonById($id)
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Person', 'find', array('id' => $id)));

        return $result ? $this->toArray($result->user) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleByIds(array $ids)
    {
        $people    = array();
        $chunk_ids = array_chunk($ids, 100);

        foreach ($chunk_ids as $chunk_ids_batch) {
            $result = $this->adapter->doRequest(Request::createCoreAPI('Person', 'find', array(
                'id' => $chunk_ids_batch,
            )));

            if (is_array($result->users)) {
                foreach ($result->users as $person) {
                    $people[] = $this->toArray($person);
                }
            }
        }

        return $people;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleFields()
    {
        $fields = array();
        $result = $this->adapter->doRequest(Request::createCoreAPI('PersonField', 'findAll'));

        if ($result) {
            foreach ($result->user_fields as $field) {
                $fields[] = $this->toArray($field);
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations()
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Organization', 'findAll'));

        return $this->toArray($result->organizations);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationById($id)
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Organization', 'find', array(
            'id' => $id,
        )));

        return $this->toArray($result->organization);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationFields()
    {
        $fields = array();
        $result = $this->adapter->doRequest(Request::createCoreAPI('OrganizationField', 'findAll'));

        if ($result) {
            foreach ($result->organization_fields as $field) {
                $fields[] = $this->toArray($field);
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsCount(DateTime $start_time = null)
    {
        $result = $this->adapter->doRequest(Request::createCoreAPI('Ticket', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $result ? $result->count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTickets(DateTime $start_time = null)
    {
        $tickets = array();
        $result  = $this->adapter->doRequest(Request::createCoreAPI('Ticket', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        if ($result) {
            foreach ($result->tickets as $ticket) {
                $tickets[] = $this->toArray($ticket);
            }
        }

        return $tickets;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsEndTime(DateTime $start_time = null)
    {
        $request = $this->adapter->doRequest(Request::createCoreAPI('Ticket', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $this->getIncrementalEndDateTime($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketComments($id)
    {
        $comments = array();
        $result   = $this->adapter->doRequest(Request::createCoreAPI('TicketComment', 'findAll', array(
            'ticket_id' => $id,
        )));

        if ($result) {
            foreach ($result->comments as $comment) {
                $comments[] = $this->toArray($comment);
            }
        }

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketFields()
    {
        $fields = array();
        $result = $this->adapter->doRequest(Request::createCoreAPI('TicketField', 'findAll'));

        $skip_types = array(
            self::FIELD_TYPE_SYSTEM_ASSIGNEE,
            self::FIELD_TYPE_SYSTEM_SUBJECT,
            self::FIELD_TYPE_SYSTEM_DESCRIPTION,
            self::FIELD_TYPE_SYSTEM_STATUS,
            self::FIELD_TYPE_SYSTEM_PRIORITY,
            self::FIELD_TYPE_SYSTEM_BASIC_PRIORITY,
            self::FIELD_TYPE_SYSTEM_GROUP,
        );

        if ($result) {
            foreach ($result->ticket_fields as $field) {
                if (in_array($field->type, $skip_types)) {
                    continue;
                }

                $fields[] = $this->toArray($field);
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleCategoryPath($section_id)
    {
        $response_categories = $this->adapter->doRequest(Request::createHelpCenter('Category', 'findAll'));
        $response_categories = $this->toArray($response_categories->categories);

        $response_sections = $this->adapter->doRequest(Request::createHelpCenter('Section', 'findAll'));
        $response_sections = $this->toArray($response_sections->sections);

        $categories = array();
        foreach ($response_categories as $category) {
            $categories[$category['id']] = $category;
        }

        $sections = array();
        foreach ($response_sections as $category) {
            $sections[$category['id']] = $category;
        }

        if (isset($sections[$section_id])) {
            $section = $sections[$section_id];
        } else {
            $response_section = $this->adapter->doRequest(Request::createHelpCenter('Section', 'find', array(
                'id' => $section_id,
            )));

            return $this->toArray($response_section->section);
        }

        if (!empty($section)) {
            if (isset($categories[$section['category_id']])) {
                $category = $categories[$section['category_id']];
            } else {
                $response_section = $this->adapter->doRequest(Request::createHelpCenter('Category', 'find', array(
                    'id' => $section['category_id'], )
                ));

                $category = $this->toArray($response_section->category);
            }

            if (!empty($category)) {
                return $category['name'].' > '.$section['name'];
            } else {
                return $section['name'];
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getArticlesCount(DateTime $start_time = null)
    {
        $result = $this->adapter->doRequest(Request::createHelpCenter('Article', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $result ? $result->count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticles(DateTime $start_time = null)
    {
        $articles = array();
        $result   = $this->adapter->doRequest(Request::createHelpCenter('Article', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        if ($result) {
            foreach ($result->articles as $article) {
                $articles[] = $this->toArray($article);
            }
        }

        return $articles;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticlesEndTime(DateTime $start_time = null)
    {
        $request = $this->adapter->doRequest(Request::createHelpCenter('Article', 'incrementalExport', array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        )));

        return $this->getIncrementalEndDateTime($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleComments($id)
    {
        $comments = array();
        $result   = $this->adapter->doRequest(Request::createHelpCenter('ArticleComment', 'findAll', array(
            'id' => $id,
        )));

        if ($result) {
            foreach ($result->comments as $comment) {
                $comments[] = $this->toArray($comment);
            }
        }

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleAttachments($id)
    {
        $attachments = array();
        $result      = $this->adapter->doRequest(Request::createHelpCenter('ArticleAttachment', 'findAll', array(
            'id' => $id,
        )));

        if ($result) {
            foreach ($result->article_attachments as $attachment) {
                $attachments[] = $this->toArray($attachment);
            }
        }

        return $attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleTranslations($id)
    {
        $translations = array();
        $result       = $this->adapter->doRequest(Request::createHelpCenter('ArticleTranslation', 'findAll', array(
            'id' => $id,
        )));

        if ($result) {
            foreach ($result->translations as $translation) {
                $translations[] = $this->toArray($translation);
            }
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticlesCategories()
    {
        $categories = array();
        $result     = $this->adapter->doRequest(Request::createHelpCenter('Category', 'findAll'));

        if ($result) {
            foreach ($result->categories as $category) {
                $categories[] = $this->toArray($category);
            }
        }

        return $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticlesSections()
    {
        $sections = array();
        $result   = $this->adapter->doRequest(Request::createHelpCenter('Section', 'findAll'));

        if ($result) {
            foreach ($result->sections as $section) {
                $section = $this->toArray($section);
                $access  = $this->adapter->doRequest(Request::createHelpCenter('SectionAccessPolicy', 'find', array(
                    'id' => $section['id'], )
                ));
                $access  = $access ? $this->toArray($access) : null;
                $section = array_merge($section, $access);

                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Converts stdClass to array.
     *
     * @param mixed $object
     *
     * @return array
     */
    private function toArray($object)
    {
        return json_decode(json_encode($object), true);
    }

    /**
     * Returns request start time timestamp.
     *
     * @param DateTime $start_time
     *
     * @return int
     */
    private function getStartTimeTimestamp(DateTime $start_time = null)
    {
        $initial_time = $this->config->getInitialTime();
        if ($start_time && $start_time > $initial_time) {
            return $start_time->getTimestamp();
        }

        return $initial_time->getTimestamp();
    }

    /**
     * Request end time timestamp to DateTime.
     *
     * @param \stdClass $request
     *
     * @return DateTime|int
     */
    private function getIncrementalEndDateTime(\stdClass $request)
    {
        if ($request) {
            $time = new DateTime();
            $time->setTimestamp($request->end_time);

            return $time;
        }

        return 0;
    }
}
