<?php

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketBuiltInFieldInterface;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Entity\TicketWorkflow;
use Application\DeskPRO\Tickets\Actions\AbstractAction;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
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

    public function getOptionUsage($type, $ids)
    {
        $action   = 'Set'.ucfirst($type);
        $criteria = 'Check'.ucfirst($type);
        $filter   = 'Filter'.ucfirst($type);

        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(TicketTrigger::class, 't')
            ->andWhere('t.terms LIKE :criteria OR t.actions LIKE :action')
            ->setParameter('criteria', '%'.$criteria.'%')
            ->setParameter('action', '%'.$action.'%')
        ;
        $triggers = $qb->getQuery()->getResult();

        $results = [];

        if ($triggers) {
            $triggers = array_filter($triggers, function ($trigger) use ($action, $criteria, $type, $ids) {
                /** @var TicketTrigger $trigger */
                foreach ($trigger->actions as $triggerAction) {
                    if ($this->filterAction($triggerAction, $action, $ids)) {
                        return true;
                    }
                }

                if ($this->filterTerms($trigger->terms->getTerms(), $criteria, $type.'_ids', $ids)) {
                    return true;
                }

                return false;
            });
            if ($triggers) {
                $results['triggers'] = $triggers;
            }
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('e')
            ->from(TicketEscalation::class, 'e')
            ->andWhere('e.terms LIKE :criteria OR e.terms_any LIKE :criteria OR e.actions LIKE :action')
            ->setParameter('criteria', '%'.$type.'%')
            ->setParameter('action', '%'.$action.'%')
        ;
        $escalations = $qb->getQuery()->getResult();

        if ($escalations) {
            $escalations = array_filter($escalations, function ($escalation) use ($action, $type, $ids) {
                /** @var $escalation $escalation */
                foreach ($escalation->actions as $escalationAction) {
                    if ($this->filterAction($escalationAction, $action, $ids)) {
                        return true;
                    }
                }

                foreach ($escalation->terms as $term) {
                    if ($this->filterTerm($term, $type, $type, $ids)) {
                        return true;
                    }
                }

                foreach ($escalation->terms_any as $term) {
                    if ($this->filterTerm($term, $type, $type, $ids)) {
                        return true;
                    }
                }

                return false;
            });
            if ($escalations) {
                $results['escalations'] = $escalations;
            }
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(Sla::class, 's')
            ->andWhere('s.apply_terms LIKE :criteria OR s.warn_actions LIKE :action OR s.fail_actions LIKE :action')
            ->setParameter('criteria', '%'.$criteria.'%')
            ->setParameter('action', '%'.$action.'%')
        ;
        $slas = $qb->getQuery()->getResult();

        if ($slas) {
            $slas = array_filter($slas, function ($sla) use ($action, $criteria, $type, $ids) {
                /** @var Sla $sla */
                foreach ($sla->warn_actions as $warnAction) {
                    if ($this->filterAction($warnAction, $action, $ids)) {
                        return true;
                    }
                }
                foreach ($sla->fail_actions as $failAction) {
                    if ($this->filterAction($failAction, $action, $ids)) {
                        return true;
                    }
                }

                if ($this->filterTerms($sla->apply_terms->getTerms(), $criteria, $type.'_ids', $ids)) {
                    return true;
                }

                return false;
            });
            if ($slas) {
                $results['slas'] = $slas;
            }
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('w')
            ->from(TicketWebhook::class, 'w')
            ->andWhere('w.searchTerms LIKE :criteria')
            ->setParameter('criteria', '%'.$type.'%')
        ;
        $webhooks = $qb->getQuery()->getResult();

        if ($webhooks) {
            $webhooks = array_filter($webhooks, function ($webhook) use ($filter, $type, $ids) {
                /** @var TicketWebhook $webhook */
                if ($this->filterTerms($webhook->getSearchTerms()->exportToArray()['terms'], $filter, $type.'_ids', $ids)) {
                    return true;
                }

                return false;
            });
            if ($webhooks) {
                $results['webhooks'] = $webhooks;
            }
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('f')
            ->from(LegacyTicketFilter::class, 'f')
            ->andWhere('f.terms LIKE :criteria')
            ->setParameter('criteria', '%'.$type.'%')
        ;
        $filters = $qb->getQuery()->getResult();

        if ($filters) {
            $filters = array_filter($filters, function ($ticketFilter) use ($filter, $type, $ids) {
                foreach ($ticketFilter->terms as $term) {
                    if ($this->filterTerm($term, $type, $type, $ids)) {
                        return true;
                    }
                }

                return false;
            });
            if ($filters) {
                $results['filters'] = $filters;
            }
        }

        return $results;
    }

    /**
     * @param AbstractAction $action
     * @param string         $match
     * @param $ids
     *
     * @return bool
     */
    private function filterAction($action, $match, $ids)
    {
        if (strstr(get_class($action), $match)) {
            $options = $action->getActionOptions();
            foreach ($options as $option) {
                if (in_array($option, $ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array  $term
     * @param string $criteria
     * @param string $type
     * @param array  $ids
     *
     * @return bool
     */
    private function filterTerm($term, $criteria, $type, $ids)
    {
        if ($term['type'] === $criteria && isset($term['options'][$type])) {
            foreach ($term['options'][$type] as $optionId) {
                if (in_array($optionId, $ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array  $terms
     * @param string $criteria
     * @param string $type
     * @param array  $ids
     *
     * @return bool
     */
    private function filterTerms($terms, $criteria, $type, $ids)
    {
        foreach ($terms as $term) {
            if (isset($term['set_terms'])) {
                foreach ($term['set_terms'] as $setTerm) {
                    if ($this->filterTerm($setTerm, $criteria, $type, $ids)) {
                        return true;
                    }
                }
            } else {
                if ($this->filterTerm($term, $criteria, $type, $ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $type
     * @param array  $removeIds
     * @param int    $setToId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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

            $this->updateOptionsUsageToId($type, $removeIds, $setToId);
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
     * @param $type
     * @param $removeIds
     * @param $setToId
     */
    protected function updateOptionsUsageToId($type, $removeIds, $setToId)
    {
        $action   = 'Set'.ucfirst($type);
        $criteria = 'Check'.ucfirst($type);
        $filter   = 'Filter'.ucfirst($type);

        $usage = $this->getOptionUsage($type, $removeIds);
        if (isset($usage['triggers'])) {
            foreach ($usage['triggers'] as $trigger) {
                foreach ($trigger->actions as $triggerAction) {
                    if ($this->filterAction($triggerAction, $action, $removeIds)) {
                        $options = $triggerAction->getActionOptions();
                        $options->set($type.'_id', $setToId);
                        $this->em->persist($trigger);
                    }
                }
                $terms = $trigger->terms->getTerms();
                if ($terms) {
                    $changed = false;
                    foreach ($terms as &$term) {
                        if (isset($term['set_terms'])) {
                            foreach ($term['set_terms'] as &$setTerm) {
                                if ($this->filterTerm($setTerm, $criteria, $type.'_ids', $removeIds)) {
                                    foreach ($setTerm['options'][$type.'_ids'] as &$option) {
                                        if (in_array($option, $removeIds)) {
                                            $option  = $setToId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $criteria, $type.'_ids', $removeIds)) {
                                foreach ($term['options'][$type.'_ids'] as &$option) {
                                    if (in_array($option, $removeIds)) {
                                        $option  = $setToId;
                                        $changed = true;
                                    }
                                }
                            }
                        }
                    }
                    if ($changed) {
                        $trigger->terms->setTerms([]);
                        foreach ($terms as $term) {
                            $trigger->terms->addTermFromArray($term);
                        }
                        $this->em->persist($trigger);
                    }
                }
            }
        }

        if (isset($usage['escalations'])) {
            foreach ($usage['escalations'] as $escalation) {
                foreach ($escalation->actions as $escalationAction) {
                    if ($this->filterAction($escalationAction, $action, $removeIds)) {
                        $options = $escalationAction->getActionOptions();
                        $options->set($type.'_id', $setToId);
                        $this->em->persist($escalation);
                    }
                }
                $changed = false;
                $terms   = $escalation->terms;
                foreach ($terms as &$term) {
                    if ($this->filterTerm($term, $type, $type, $removeIds)) {
                        foreach ($term['options'][$type] as &$option) {
                            $option  = (string) $setToId;
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    $escalation->terms = $terms;
                    $this->em->persist($escalation);
                }
                $terms = $escalation->terms_any;
                foreach ($terms as &$term) {
                    if ($this->filterTerm($term, $type, $type, $removeIds)) {
                        foreach ($term['options'][$type] as &$option) {
                            $option  = (string) $setToId;
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    $escalation->terms_any = $terms;
                    $this->em->persist($escalation);
                }
            }
        }

        if (isset($usage['slas'])) {
            foreach ($usage['slas'] as $sla) {
                foreach ($sla->warn_actions as $warnAction) {
                    if ($this->filterAction($warnAction, $action, $removeIds)) {
                        $options = $warnAction->getActionOptions();
                        $options->set($type.'_id', $setToId);
                        $this->em->persist($sla);
                    }
                }
                foreach ($sla->fail_actions as $failAction) {
                    if ($this->filterAction($failAction, $action, $removeIds)) {
                        $options = $failAction->getActionOptions();
                        $options->set($type.'_id', $setToId);
                        $this->em->persist($sla);
                    }
                }
                $terms = $sla->apply_terms->getTerms();
                if ($terms) {
                    $changed = false;
                    foreach ($terms as &$term) {
                        if (isset($term['set_terms'])) {
                            foreach ($term['set_terms'] as &$setTerm) {
                                if ($this->filterTerm($setTerm, $criteria, $type.'_ids', $removeIds)) {
                                    foreach ($setTerm['options'][$type.'_ids'] as &$option) {
                                        if (in_array($option, $removeIds)) {
                                            $option  = (string) $setToId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $criteria, $type.'_ids', $removeIds)) {
                                foreach ($term['options'][$type.'_ids'] as &$option) {
                                    if (in_array($option, $removeIds)) {
                                        $option  = (string) $setToId;
                                        $changed = true;
                                    }
                                }
                            }
                        }
                    }
                    if ($changed) {
                        $sla->apply_terms->setTerms([]);
                        foreach ($terms as $term) {
                            $sla->apply_terms->addTermFromArray($term);
                        }
                        $this->em->persist($sla);
                    }
                }
            }
        }
        if (isset($usage['webhooks'])) {
            foreach ($usage['webhooks'] as $webhook) {
                $terms = $webhook->getSearchTerms()->exportToArray()['terms'];
                if ($terms) {
                    $changed = false;
                    foreach ($terms as &$term) {
                        if (isset($term['set_terms'])) {
                            foreach ($term['set_terms'] as &$setTerm) {
                                if ($this->filterTerm($setTerm, $filter, $type.'_ids', $removeIds)) {
                                    foreach ($setTerm['options'][$type.'_ids'] as &$option) {
                                        if (in_array($option, $removeIds)) {
                                            $option  = (string) $setToId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $filter, $type.'_ids', $removeIds)) {
                                foreach ($term['options'][$type.'_ids'] as &$option) {
                                    if (in_array($option, $removeIds)) {
                                        $option  = (string) $setToId;
                                        $changed = true;
                                    }
                                }
                            }
                        }
                    }
                    if ($changed) {
                        $filterTerms = new FilterTerms();
                        $filterTerms->importFromArray(['terms' => $terms]);
                        $webhook->setSearchTerms($filterTerms);
                        $this->em->persist($webhook);
                    }
                }
            }
        }

        if (isset($usage['filters'])) {
            foreach ($usage['filters'] as $ticketFilter) {
                $terms = $ticketFilter->terms;
                foreach ($terms as &$term) {
                    if ($this->filterTerm($term, $type, $type, $removeIds)) {
                        foreach ($term['options'][$type] as &$option) {
                            $option  = (string) $setToId;
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    $ticketFilter->terms = $terms;
                    $this->em->persist($ticketFilter);
                }
            }
        }
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
            'category' => TicketCategory::class,
            'product'  => Product::class,
            'workflow' => TicketWorkflow::class,
            'priority' => TicketPriority::class,
        ];

        return $this->em->getRepository($map[$type]);
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
