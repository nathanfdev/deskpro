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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;

/**
 * Class Organizations
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
class Organizations extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ORGANIZATION;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getOrganizationReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $organizations = $this->getReaderData($this->getOrganizationReaderConfig());
        $contact_data  = $this->exportContactData();

        return $collection;
    }

    /**
     * @param array $organization
     * @return Entity\Organization|null
     */
    private function exportOrganization(array $organization)
    {
        if ($this->isOrganizationValid($organization)) {
            $entity = new Entity\Organization();

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of organizations contact data
     *
     * @return Entity\Collection
     */
    private function exportContactData()
    {
        $collection   = new Entity\Collection();
        $contact_data = $this->getReaderData($this->getOrganizationContactDataReaderConfig());

        return $collection;
    }

    /**
     * @param array $contact
     * @return Entity\OrganizationContactData|null
     */
    private function exportContact(array $contact)
    {
        if ($this->isContactValid($contact)) {
            $entity = new Entity\OrganizationContactData();

            return $entity;
        }

        return null;
    }

    /**
     * Returns reader config for organization records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS);
    }

    /**
     * Returns reader config for organization contact data records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationContactDataReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS_CONTACT_DATA);
    }

    /**
     * Check if organization has all required columns
     *
     * @param array $organization
     * @return bool
     */
    private function isOrganizationValid(array $organization)
    {
        $columns = array(
            'name',
            'importance',
        );

        return $this->hasRequiredColumns($organization, $columns);
    }

    /**
     * Check if organization contact data has all required columns
     *
     * @param array $contact
     * @return bool
     */
    private function isContactValid(array $contact)
    {
        $columns = array(
            'contact_type',
            'comment',
        );

        return $this->hasRequiredColumns($contact, $columns);
    }
}
