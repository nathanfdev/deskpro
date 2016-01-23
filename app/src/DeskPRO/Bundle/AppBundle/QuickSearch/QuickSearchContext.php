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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class QuickSearchContext.
 */
class QuickSearchContext
{
    const TYPE_ARTICLE           = 'article';
    const TYPE_DOWNLOAD          = 'download';
    const TYPE_FEEDBACK          = 'feedback';
    const TYPE_NEWS              = 'news';
    const TYPE_TICKET            = 'ticket';
    const TYPE_PERSON            = 'person';
    const TYPE_ORGANIZATION      = 'organization';
    const TYPE_CHAT_CONVERSATION = 'chat_conversation';

    /**
     * @var string
     */
    private $type;

    /**
     * @var QuickSearchResponse
     */
    private $response;

    /**
     * @var ArrayCollection
     */
    public $ids;

    /**
     * @var array
     */
    public $entities;

    /**
     * Constructor.
     *
     * @param string              $type
     * @param QuickSearchResponse $response
     */
    public function __construct($type, QuickSearchResponse $response)
    {
        $this->type     = $type;
        $this->ids      = new ArrayCollection();
        $this->entities = new ArrayCollection();
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return QuickSearchResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public static function getDoctrineMapping()
    {
        return [
            self::TYPE_ARTICLE           => 'DeskPRO:Article',
            self::TYPE_DOWNLOAD          => 'DeskPRO:Download',
            self::TYPE_FEEDBACK          => 'DeskPRO:Feedback',
            self::TYPE_NEWS              => 'DeskPRO:News',
            self::TYPE_TICKET            => 'DeskPRO:Ticket',
            self::TYPE_PERSON            => 'DeskPRO:Person',
            self::TYPE_ORGANIZATION      => 'DeskPRO:Organization',
            self::TYPE_CHAT_CONVERSATION => 'DeskPRO:ChatConversation',
        ];
    }
}
