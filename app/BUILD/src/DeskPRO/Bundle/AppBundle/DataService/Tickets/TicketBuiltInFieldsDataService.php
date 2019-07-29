<?php

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketBuiltInFieldInterface;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Doctrine\ORM\EntityManager;

/**
 * Provides controller data access to the ticket product/priority/workflow/category.
 */
class TicketBuiltInFieldsDataService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var BrandAwareSettingsResolver
     */
    protected $brandAwareSettings;

    /**
     * @param EntityManager              $em
     * @param BrandAwareSettingsResolver $brandAwareSettings
     */
    public function __construct(EntityManager $em, BrandAwareSettingsResolver $brandAwareSettings)
    {
        $this->em                 = $em;
        $this->brandAwareSettings = $brandAwareSettings;
    }

    /**
     * @param string $type
     *
     * @return TicketBuiltInFieldInterface[]
     */
    public function getAll($type)
    {
        $this->checkTypeOrException($type);

        return $this->getRepository($type)->findAll();
    }

    /**
     * @param string $type
     * @param array  $ids
     *
     * @return int
     */
    public function getTicketsCountWithFields($type, $ids)
    {
        $this->checkTypeOrException($type);

        return $this->em->getRepository(Ticket::class)->getCountByTicketFieldIds($type, $ids);
    }

    /**
     * @param string $type
     * @param array  $removeIds
     * @param int    $setToId
     *
     * @return mixed
     */
    public function deleteOptionsById($type, $removeIds, $setToId = null)
    {
        $this->checkTypeOrException($type);

        if (!$removeIds) {
            return;
        }

        if ($setToId) {
            $this->em->getRepository(Ticket::class)->updateTicketFieldsTo($type, $removeIds, $setToId);
        }

        $options = $this->getRepository($type)->findById($removeIds);
        foreach ($options as $option) {
            $this->em->remove($option);
        }

        $defaultSettingName = $this->getDefaultSettingName($type);
        $defaultId          = $this->brandAwareSettings->getSetting($defaultSettingName);
        if ($defaultId && in_array($defaultId, $removeIds)) {
            $setting = $this->em->getRepository(Setting::class)->findOneByName($defaultSettingName);
            if ($setting) {
                $this->em->remove($setting);
            }
        }

        $this->em->flush();
    }

    /**
     * @param string $type
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($type)
    {
        $this->checkTypeOrException($type);

        $map = [
            'category' => 'TicketCategory',
            'product'  => 'Product',
            'workflow' => 'TicketWorkflow',
            'priority' => 'TicketPriority',
        ];

        return $this->em->getRepository('DeskPRO:'.$map[$type]);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getDefaultSettingName($type)
    {
        $this->checkTypeOrException($type);

        $settings = [
            'category' => 'core.default_ticket_cat',
            'product'  => 'core.default_prod_id',
            'workflow' => 'core.default_ticket_work',
            'priority' => 'core.default_ticket_pri',
        ];

        return $settings[$type];
    }

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    protected function checkTypeOrException($type)
    {
        if (!in_array($type, ['category', 'product', 'workflow', 'priority'])) {
            throw new \InvalidArgumentException(sprintf('Wrong ticket built-in field type `%s`', $type));
        }
    }
}
