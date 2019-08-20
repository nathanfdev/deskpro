<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\CustomDefTicket as CustomDefTicketEntity;
use Application\DeskPRO\Entity\LegacyTicketFilter as LegacyTicketFilterEntity;
use Application\DeskPRO\Entity\Sla as SlaEntity;
use Application\DeskPRO\Entity\TicketEscalation as TicketEscalationEntity;
use Application\DeskPRO\Entity\TicketTrigger as TicketTriggerEntity;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;

class CustomDefTicket extends CustomDefAbstract
{
    public function getOptionUsage(CustomDefTicketEntity $field, $ids)
    {
        $fieldId  = $field->getId();
        $criteria = 'CheckTicketField'.$fieldId;
        $action   = 'SetTicketField';
        $filter   = 'FilterTicketField'.$fieldId;

        $qb = $this->_em->createQueryBuilder();
        $qb->select('t')
            ->from(TicketTriggerEntity::class, 't')
            ->andWhere('t.terms LIKE :criteria OR t.actions LIKE :action')
            ->setParameter('criteria', '%'.$criteria.'%')
            ->setParameter('action', '%'.$action.$fieldId.'%')
        ;
        $triggers = $qb->getQuery()->getResult();

        $results = [];

        if ($triggers) {
            $triggers = array_filter($triggers, function ($trigger) use ($action, $criteria, $fieldId, $ids) {
                /** @var TicketTriggerEntity $trigger */
                foreach ($trigger->actions as $triggerAction) {
                    if ($this->filterAction($triggerAction, $action, $fieldId, $ids)) {
                        return true;
                    }
                }

                if ($this->filterTerms($trigger->terms->getTerms(), $criteria, $ids)) {
                    return true;
                }

                return false;
            });
            if ($triggers) {
                $results['triggers'] = $triggers;
            }
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
            ->from(TicketEscalationEntity::class, 'e')
            ->andWhere('e.terms LIKE :criteria OR e.terms_any LIKE :criteria OR e.actions LIKE :action')
            ->setParameter('criteria', '%ticket_field['.$fieldId.']%')
            ->setParameter('action', '%'.$action.'%')
        ;
        $escalations = $qb->getQuery()->getResult();

        if ($escalations) {
            $escalations = array_filter($escalations, function ($escalation) use ($action, $fieldId, $ids) {
                /** @var $escalation $escalation */
                foreach ($escalation->actions as $escalationAction) {
                    if ($this->filterAction($escalationAction, $action, $fieldId, $ids)) {
                        return true;
                    }
                }

                foreach ($escalation->terms as $term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as $optionId) {
                            if (in_array($optionId, $ids)) {
                                return true;
                            }
                        }
                    }
                }

                foreach ($escalation->terms_any as $term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as $optionId) {
                            if (in_array($optionId, $ids)) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            });
            if ($escalations) {
                $results['escalations'] = $escalations;
            }
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('s')
            ->from(SlaEntity::class, 's')
            ->andWhere('s.apply_terms LIKE :criteria OR s.warn_actions LIKE :action OR s.fail_actions LIKE :action')
            ->setParameter('criteria', '%'.$criteria.'%')
            ->setParameter('action', '%'.$action.'%')
        ;
        $slas = $qb->getQuery()->getResult();

        if ($slas) {
            $slas = array_filter($slas, function ($sla) use ($action, $criteria, $fieldId, $ids) {
                /** @var SlaEntity $sla */
                foreach ($sla->warn_actions as $warnAction) {
                    if ($this->filterAction($warnAction, $action, $fieldId, $ids)) {
                        return true;
                    }
                }
                foreach ($sla->fail_actions as $failAction) {
                    if ($this->filterAction($failAction, $action, $fieldId, $ids)) {
                        return true;
                    }
                }

                if ($this->filterTerms($sla->apply_terms->getTerms(), $criteria, $ids)) {
                    return true;
                }

                return false;
            });
            if ($slas) {
                $results['slas'] = $slas;
            }
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('w')
            ->from(TicketWebhook::class, 'w')
            ->andWhere('w.searchTerms LIKE :criteria')
            ->setParameter('criteria', '%ticket_field['.$fieldId.']%')
        ;
        $webhooks = $qb->getQuery()->getResult();

        if ($webhooks) {
            $webhooks = array_filter($webhooks, function ($webhook) use ($filter, $fieldId, $ids) {
                /** @var TicketWebhook $webhook */
                if ($this->filterTerms($webhook->getSearchTerms()->exportToArray()['terms'], $filter, $ids)) {
                    return true;
                }

                return false;
            });
            if ($webhooks) {
                $results['webhooks'] = $webhooks;
            }
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from(LegacyTicketFilterEntity::class, 'f')
            ->andWhere('f.terms LIKE :criteria')
            ->setParameter('criteria', '%ticket_field['.$fieldId.']%')
        ;
        $filters = $qb->getQuery()->getResult();

        if ($filters) {
            $filters = array_filter($filters, function ($ticketFilter) use ($filter, $fieldId, $ids) {
                foreach ($ticketFilter->terms as $term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as $optionId) {
                            if (in_array($optionId, $ids)) {
                                return true;
                            }
                        }
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

    protected function updateOptionsUsageToId($field, $fromIds, $toId)
    {
        $fieldId  = $field->getId();
        $criteria = 'CheckTicketField'.$fieldId;
        $action   = 'SetTicketField';
        $filter   = 'FilterTicketField'.$fieldId;

        $usage = $this->getOptionUsage($field, $fromIds);
        if (isset($usage['triggers'])) {
            foreach ($usage['triggers'] as $trigger) {
                foreach ($trigger->actions as $triggerAction) {
                    if ($this->filterAction($triggerAction, $action, $fieldId, $fromIds)) {
                        $options = $triggerAction->getActionOptions();
                        $options->set('value', $toId);
                        $this->_em->persist($trigger);
                    }
                }
                $terms = $trigger->terms->getTerms();
                if ($terms) {
                    $changed = false;
                    foreach ($terms as &$term) {
                        if (isset($term['set_terms'])) {
                            foreach ($term['set_terms'] as &$setTerm) {
                                if ($this->filterTerm($setTerm, $criteria, $fromIds)) {
                                    foreach ($setTerm['options']['value'] as &$option) {
                                        if (in_array($option, $fromIds)) {
                                            $option  = $toId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $criteria, $fromIds)) {
                                foreach ($term['options']['value'] as &$option) {
                                    if (in_array($option, $fromIds)) {
                                        $option  = $toId;
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
                        $this->_em->persist($trigger);
                    }
                }
            }
        }

        if (isset($usage['escalations'])) {
            foreach ($usage['escalations'] as $escalation) {
                foreach ($escalation->actions as $escalationAction) {
                    if ($this->filterAction($escalationAction, $action, $fieldId, $fromIds)) {
                        $options = $escalationAction->getActionOptions();
                        $options->set('value', $toId);
                        $this->_em->persist($escalation);
                    }
                }
                $changed = false;
                $terms   = $escalation->terms;
                foreach ($terms as &$term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as &$option) {
                            if (in_array($option, $fromIds)) {
                                $option  = (string) $toId;
                                $changed = true;
                            }
                        }
                    }
                }
                if ($changed) {
                    $escalation->terms = $terms;
                    $this->_em->persist($escalation);
                }
                $changed = false;
                $terms   = $escalation->terms_any;
                foreach ($terms as &$term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as &$option) {
                            if (in_array($option, $fromIds)) {
                                $option  = (string) $toId;
                                $changed = true;
                            }
                        }
                    }
                }
                if ($changed) {
                    $escalation->terms_any = $terms;
                    $this->_em->persist($escalation);
                }
            }
        }

        if (isset($usage['slas'])) {
            foreach ($usage['slas'] as $sla) {
                foreach ($sla->warn_actions as $warnAction) {
                    if ($this->filterAction($warnAction, $action, $fieldId, $fromIds)) {
                        $options = $warnAction->getActionOptions();
                        $options->set('value', $toId);
                        $this->_em->persist($sla);
                    }
                }
                foreach ($sla->fail_actions as $failAction) {
                    if ($this->filterAction($failAction, $action, $fieldId, $fromIds)) {
                        $options = $failAction->getActionOptions();
                        $options->set('value', $toId);
                        $this->_em->persist($sla);
                    }
                }
                $terms = $sla->apply_terms->getTerms();
                if ($terms) {
                    $changed = false;
                    foreach ($terms as &$term) {
                        if (isset($term['set_terms'])) {
                            foreach ($term['set_terms'] as &$setTerm) {
                                if ($this->filterTerm($setTerm, $criteria, $fromIds)) {
                                    foreach ($setTerm['options']['value'] as &$option) {
                                        if (in_array($option, $fromIds)) {
                                            $option  = (string) $toId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $criteria, $fromIds)) {
                                foreach ($term['options']['value'] as &$option) {
                                    if (in_array($option, $fromIds)) {
                                        $option  = (string) $toId;
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
                        $this->_em->persist($sla);
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
                                if ($this->filterTerm($setTerm, $filter, $fromIds)) {
                                    foreach ($setTerm['options']['value'] as &$option) {
                                        if (in_array($option, $fromIds)) {
                                            $option  = (string) $toId;
                                            $changed = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->filterTerm($term, $filter, $fromIds)) {
                                foreach ($term['options']['value'] as &$option) {
                                    if (in_array($option, $fromIds)) {
                                        $option  = (string) $toId;
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
                        $this->_em->persist($webhook);
                    }
                }
            }
        }

        if (isset($usage['filters'])) {
            foreach ($usage['filters'] as $ticketFilter) {
                $changed = false;
                $terms   = $ticketFilter->terms;
                foreach ($terms as &$term) {
                    if ($term['type'] === 'ticket_field['.$fieldId.']' && isset($term['options']['custom_fields']['field_'.$fieldId])) {
                        foreach ($term['options']['custom_fields']['field_'.$fieldId] as &$option) {
                            if (in_array($option, $fromIds)) {
                                $option  = (string) $toId;
                                $changed = true;
                            }
                        }
                    }
                }
                if ($changed) {
                    $ticketFilter->terms = $terms;
                    $this->_em->persist($ticketFilter);
                }
            }
        }
    }
}
