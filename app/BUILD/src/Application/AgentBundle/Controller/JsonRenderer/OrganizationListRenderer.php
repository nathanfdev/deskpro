<?php

/**
 * DeskPRO.
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
        $this->em        = $container->getEm();
        $this->db        = $container->getDb();
    }

    /**
     * @param OrgResultsDisplay $display
     * @param null              $fn_visitor
     *
     * @return array
     */
    public function renderArray(OrgResultsDisplay $display, $fn_visitor = null)
    {
        if (!$display->getCount()) {
            return [];
        }

        //------------------------------
        // Generate data array
        //------------------------------

        $json_array = [];

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
     * @param OrgResultsDisplay $display
     *
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
        $data = [];

        $data['id']             = $entity['id'];
        $data['name']           = $entity['name'];
        $data['members_count']  = $display->getOrgMemberCount($entity);
        $data['picture_url_15'] = $entity->getPictureUrl(15);
        $data['labels']         = $display->getOrgLabels($entity);

        $custom_data = $display->getFieldsData($entity);
        if ($custom_data) {
            $field_manager = $this->container->getOrgFieldManager();

            $rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
            foreach ($rendered_data as $fid => $v) {
                $data['organization_fields['.$fid.']'] = [
                    'title' => $v['title'],
                    'value' => $v['rendered'],
                ];
            }
        }

        return $data;
    }
}
