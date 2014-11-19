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
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Organizations\OrgResultsDisplay;
use Application\DeskPRO\Util;

class OrganizationListRenderer
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


    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->em = $container->getEm();
        $this->db = $container->getDb();
    }


    /**
     * @param  OrgResultsDisplay $display
     * @param  null              $fn_visitor
     * @return array
     */
    public function renderArray(OrgResultsDisplay $display, $fn_visitor = null)
    {
        if (!$display->getCount()) {
            return array();
        }

        #------------------------------
        # Generate data array
        #------------------------------

        $json_array = array();

        foreach ($display->getOrganizations() as $org) {
            $data = $this->renderOrganization($org, $display);

            if ($fn_visitor) {
                $data = call_user_func($fn_visitor, $org, $data);
            }

            $json_array[] = $data;
        }

        return $json_array;
    }


    /**
     * @param  OrgResultsDisplay $display
     * @return string
     */
    public function renderJson(OrgResultsDisplay $display)
    {
        if (!$display->getCount()) {
            return '[]';
        }

        return Util::jsonEncode($this->renderArray($display));
    }

    private function renderOrganization(Organization $entity, OrgResultsDisplay $display)
    {
        $data = array();

        $data['id']                    = $entity['id'];
        $data['name']                  = $entity['name'];
        $data['members_count']         = $display->getOrgMemberCount($entity);
        $data['picture_url_15']        = $entity->getPictureUrl(15);
        $data['labels']                = $display->getOrgLabels($entity);


        $custom_data = $display->getFieldsData($entity);
        if ($custom_data) {
            $field_manager = $this->container->getOrgFieldManager();

            $rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
            foreach ($rendered_data as $fid => $v) {
                $data['organization_fields[' . $fid . ']'] = array(
                    'title' => $v['title'],
                    'value' => $v['rendered'],
                );
            }
        }

        return $data;
    }
}
