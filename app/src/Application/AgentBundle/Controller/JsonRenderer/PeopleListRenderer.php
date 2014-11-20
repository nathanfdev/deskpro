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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\AgentBundle\Controller\JsonRenderer;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PeopleResultsDisplay;
use Application\DeskPRO\Util;
use Orb\Util\Arrays;

class PeopleListRenderer
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\DeskPRO\Entity\Organization[]
     */
    private $cache_orgs;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->em        = $container->getEm();
        $this->db        = $container->getDb();
    }

    /**
     * @param  PeopleResultsDisplay $display
     * @param  null                 $fn_visitor
     * @return array
     */
    public function renderArray(PeopleResultsDisplay $display, $fn_visitor = null)
    {
        if (!$display->getCount()) {
            return array();
        }

        #------------------------------
        # Precache data
        #------------------------------

        $org_ids    = array();
        $person_ids = array();

        foreach ($display->getPeople() as $person) {
            $person_ids[] = $person->id;
            if ($person->organization) {
                $org_ids[] = $person->organization->id;
            }
        }

        if ($org_ids) {
            $this->cache_orgs = $this->em->getRepository('DeskPRO:Organization')->getByIds($org_ids);
            $this->cache_orgs = Arrays::keyFromData($this->cache_orgs, 'id');
        }

        #------------------------------
        # Generate data array
        #------------------------------

        $json_array = array();

        foreach ($display->getPeople() as $person) {
            $data = $this->renderPerson($person, $display);

            if ($fn_visitor) {
                $data = call_user_func($fn_visitor, $person, $data);
            }

            $json_array[] = $data;
        }

        return $json_array;
    }

    /**
     * @param  PeopleResultsDisplay $display
     * @return string
     */
    public function renderJson(PeopleResultsDisplay $display)
    {
        if (!$display->getCount()) {
            return '[]';
        }

        return Util::jsonEncode($this->renderArray($display));
    }

    /**
     * @param  Person               $entity
     * @param  PeopleResultsDisplay $display
     * @return array
     */
    private function renderPerson(Person $entity, PeopleResultsDisplay $display)
    {
        $data = array();

        $data['id']                    = $entity->id;
        $data['name_with_title']       = $entity->getNameWithTitle();
        $data['organization']          = $entity->organization ? array('name' => $entity->organization['name']) : null;
        $data['is_contact']            = $entity->is_contact;
        $data['is_user']               = $entity->is_user;
        $data['is_agent']              = $entity->is_agent;
        $data['was_agent']             = $entity->was_agent;
        $data['can_agent']             = $entity->can_agent;
        $data['can_admin']             = $entity->can_admin;
        $data['is_confirmed']          = $entity->is_confirmed;
        $data['is_agent_confirmed']    = $entity->is_agent_confirmed;
        $data['is_deleted']            = $entity->is_deleted;
        $data['is_disabled']           = $entity->is_disabled;
        $data['creation_system']       = $entity->creation_system;
        $data['name']                  = $entity->name;
        $data['first_name']            = $entity->first_name;
        $data['last_name']             = $entity->last_name;
        $data['title_prefix']          = $entity->title_prefix;
        $data['override_display_name'] = $entity->override_display_name;
        $data['summary']               = $entity->summary;
        $data['organization_position'] = $entity->organization_position;
        $data['organization_manager']  = $entity->organization_manager;
        $data['timezone']              = $entity->timezone;

        $data['date_created']    = $entity->date_created->format('Y-m-d H:i:s');
        $data['date_created_ts'] = $entity->date_created->getTimestamp();

        $data['display_name']  = $entity->getDisplayName();
        if ($entity->primary_email) {
            $data['primary_email'] = array(
                'id'    => $entity->primary_email->id,
                'email' => $entity->primary_email->email,
            );
        }

        $email                 = $display->getEmail($entity);
        $data['email']         = $email ? $email['email'] : null;
        $data['usernames']     = $display->getPersonUsernames($entity);
        $data['language']      = $entity->language ? $entity->language->title : null;
        $data['labels']        = $display->getPersonLabels($entity);
        $data['tickets_count'] = $display->getPersonTicketCount($entity);

        $data['picture_url']    = $entity->getPictureUrl();
        $data['picture_url_80'] = $entity->getPictureUrl(80);
        $data['picture_url_64'] = $entity->getPictureUrl(64);
        $data['picture_url_50'] = $entity->getPictureUrl(50);
        $data['picture_url_45'] = $entity->getPictureUrl(45);
        $data['picture_url_32'] = $entity->getPictureUrl(32);
        $data['picture_url_22'] = $entity->getPictureUrl(22);
        $data['picture_url_16'] = $entity->getPictureUrl(16);

        $custom_data = $display->getUserFieldData($entity);
        if ($custom_data) {
            $field_manager = $this->container->getPersonFieldManager();

            $rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
            foreach ($rendered_data as $fid => $v) {
                $data['person_fields['.$fid.']'] = array(
                    'title' => $v['title'],
                    'value' => $v['rendered'],
                );
            }
        }

        return $data;
    }
}
