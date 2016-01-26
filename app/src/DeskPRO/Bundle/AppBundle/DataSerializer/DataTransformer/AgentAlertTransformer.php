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

namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;

class AgentAlertTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param DataTransformerRequest $transformation_request
     *
     * @return mixed
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [];
    }

    /**
     * @param DataTransformerRequest $transformation_request
     *
     * @return mixed
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /* @var \Application\DeskPRO\Entity\AgentAlert $data */
        $alert     = $transformation_request->getDataToBeTransformed();
        $data      = $alert->getData();
        $alertData = [];
        $notifData = [
            'title'   => '',
            'summary' => '',
        ];
        $type = 'notifications.'.$alert->getTypename();

        switch ($alert->getTypename()) {
            case 'tickets':
                $alertData['ticket'] = $data['ticket'];
                if ($data['is_new_ticket']) {
                    $type .= '.new_ticket';
                } elseif ($data['is_new_agent_reply']) {
                    $type .= '.new_message.agent_reply';
                } elseif ($data['is_new_agent_note']) {
                    $type .= '.new_message.agent_note';
                } elseif ($data['is_new_user_reply']) {
                    $type .= '.new_message.user_reply';
                } else {
                    $type .= '.updated';
                }
                break;
        }

        $title = Strings::extractRegexMatch('#<big>(.*?)</big>#s', $data['browser_rendered']);
        $title = preg_replace('#<span[^>]*>.*?</span>#s', '', $title);
        $title = $this->cleanString($title);

        $summary = Strings::extractRegexMatch('#<small>(.*?)</small>#s', $data['browser_rendered']);
        $summary = $this->cleanString($summary);

        $notifData['title']   = $title;
        $notifData['summary'] = $summary;

        $alertData['notification'] = $notifData;
        $alertData['performer']    = $data['performer'];

        return [
            'uuid'         => (string) $alert->getId(),
            'type'         => $type,
            'data'         => $alertData,
            'date_created' => $alert->date_created,
            'is_dismissed' => (bool) $alert->is_dismissed,
        ];
    }

    private function cleanString($str)
    {
        $str = Strings::decodeHtmlEntities($str);
        $str = Strings::removeInvisibleCharacters($str);
        $str = Strings::removeLineBreaks($str);
        $str = str_replace("\t", ' ', $str);
        $str = preg_replace('#\s{,2}#', ' ', $str);
        $str = trim($str);

        return $str;
    }
}
