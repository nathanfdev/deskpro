<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermComposite;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermInterface;
use Application\DeskPRO\Util as DeskPROUtil;
use Orb\Util\OptionsArray;
use Orb\Util\Util;

/**
 * This converts between 'new' and 'old' style term definitions.
 *
 * New style terms are built using FilterTerms and one object per term. This allows for better type-checking
 * and better organisation of term logic.
 *
 * Old style is just a plain array of terms. We started to replace it (like we did with Triggers with TriggerActions),
 * but ran out of time because it affects so many things: ticket searching, escalations, filters.
 *
 * The admin interface is built up around the idea of the new system though, it's how it accepts and processes
 * forms for filters and escalations. So we need this transformer to convert new-style into old-style and vice versa.
 */
class LegacyTermsTransformer
{
    /**
     * Convert FilterTerms into legacy-style terms array.
     *
     * @param FilterTerms $terms
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function toLegacyTerms(FilterTerms $terms)
    {
        $legacy_terms = [];

        $all_terms = $terms->getTerms();

        foreach ($all_terms as $term) {
            $t = $this->_termToLegacyTerms($term);

            if (!$t) {
                throw new \InvalidArgumentException('New term has no mapping to legacy term: '.get_class($term));
            }

            if (isset($t['type'])) {
                $t = [$t];
            }

            $legacy_terms = array_merge($legacy_terms, $t);
        }

        return $legacy_terms;
    }

    /**
     * @param CriteriaTermInterface $term
     *
     * @return array Returns an array of replacement terms (usually only one, but possibly multiple if there is a non-exact match)
     */
    private function _termToLegacyTerms($term)
    {
        $legacy_terms = [];

        if ($term instanceof FilterTermComposite) {
            foreach ($term->getAll() as $subterm) {
                $t = $this->_termToLegacyTerms($subterm);
                if ($t) {
                    $legacy_terms = array_merge($legacy_terms, $t);
                }
            }

            return $legacy_terms;
        }

        $options = $term->getTermOptions();

        switch (Util::getBaseClassname($term)) {
            case 'FilterAgent':
                return [
                    'type'    => 'agent',
                    'op'      => $term->getTermOperator(),
                    'options' => ['agent' => $options['agent_ids']],
                ];

            case 'FilterAgentParticipant':
                return [
                    'type'    => 'participant',
                    'op'      => $term->getTermOperator(),
                    'options' => ['agent' => $options['agent_ids']],
                ];

            case 'FilterAgentTeam':
                return [
                    'type'    => 'agent_team',
                    'op'      => $term->getTermOperator(),
                    'options' => ['agent_team' => $options['team_ids']],
                ];

            case 'FilterCategory':
                return [
                    'type'    => 'category',
                    'op'      => $term->getTermOperator(),
                    'options' => ['category' => $options['category_ids']],
                ];

            case 'FilterBrand':
                return [
                    'type'    => 'brand',
                    'op'      => $term->getTermOperator(),
                    'options' => ['brand' => $options['brand_ids']],
                ];

            case 'FilterDepartment':
                return [
                    'type'    => 'department',
                    'op'      => $term->getTermOperator(),
                    'options' => ['department' => $options['department_ids']],
                ];

            case 'FilterHoldStatus':
                return [
                    'type'    => 'is_hold',
                    'op'      => $term->getTermOperator(),
                    'options' => ['is_hold' => $options['is_hold']],
                ];

            case 'FilterLabels':
                $labels = DeskPROUtil::labelsArrayFromString($options['labels']);

                return [
                    'type'    => 'label',
                    'op'      => $term->getTermOperator(),
                    'options' => ['label' => $labels],
                ];

            case 'FilterLanguage':
                return [
                    'type'    => 'language',
                    'op'      => $term->getTermOperator(),
                    'options' => ['language' => $options['language_ids']],
                ];

            case 'FilterOrgEmailDomain':
                return [
                    'type'    => 'org_email_domain',
                    'op'      => $term->getTermOperator(),
                    'options' => ['email_domain' => $options['domain']],
                ];

            case 'FilterOrgId':
                return [
                    'type'    => 'organization',
                    'op'      => $term->getTermOperator(),
                    'options' => ['organization' => implode(',', (array) @$options['id'] ?: [])],
                ];

            case 'FilterOrgLabels':
                $labels = DeskPROUtil::labelsArrayFromString($options['labels']);

                return [
                    'type'    => 'org_label',
                    'op'      => $term->getTermOperator(),
                    'options' => ['labels' => $labels],
                ];

            case 'FilterPriority':
                return [
                    'type'    => 'priority',
                    'op'      => $term->getTermOperator(),
                    'options' => ['priority' => $options['priority_ids']],
                ];

            case 'FilterProduct':
                return [
                    'type'    => 'product',
                    'op'      => $term->getTermOperator(),
                    'options' => ['product' => $options['product_ids']],
                ];

            case 'FilterStatus':
                return [
                    'type'    => 'status',
                    'op'      => $term->getTermOperator(),
                    'options' => ['status' => $options['status']],
                ];

            case 'FilterSubject':
                return [
                    'type'    => 'subject',
                    'op'      => $term->getTermOperator(),
                    'options' => ['subject' => $options['subject']],
                ];

            case 'FilterFeedbackRating':
                return [
                    'type'    => 'feedback_rating',
                    'op'      => $term->getTermOperator(),
                    'options' => ['rating' => $options['rating']],
                ];

            case 'FilterUrgency':
                return [
                    'type'    => 'urgency',
                    'op'      => $term->getTermOperator(),
                    'options' => ['num' => $options['urgency']],
                ];

            case 'FilterUserEmailAddress':
                if ($options['email'][0] == '@') {
                    return [
                        'type'    => 'person_email_domain',
                        'op'      => $term->getTermOperator(),
                        'options' => ['email_domain' => substr($options['email'], 1)],
                    ];
                } else {
                    return [
                        'type'    => 'person_email',
                        'op'      => $term->getTermOperator(),
                        'options' => ['email' => $options['email']],
                    ];
                }

            case 'FilterUserGroups':
                return [
                    'type'    => 'person_usergroup',
                    'op'      => $term->getTermOperator(),
                    'options' => ['usergroup' => $options['group_ids']],
                ];

            case 'FilterUserLabels':
                $labels = DeskPROUtil::labelsArrayFromString($options['labels']);

                return [
                    'type'    => 'person_label',
                    'op'      => $term->getTermOperator(),
                    'options' => ['labels' => $labels],
                ];

            case 'FilterWorkflow':
                return [
                    'type'    => 'workflow',
                    'op'      => $term->getTermOperator(),
                    'options' => ['workflow' => $options['workflow_ids']],
                ];

            case 'FilterEmailAccount':
                return [
                    'type'    => 'email_account',
                    'op'      => $term->getTermOperator(),
                    'options' => ['email_account_ids' => $options['email_account_ids']],
                ];

            case 'FilterSlaStatus':
                return [
                    'type'    => 'sla_status',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterSla':
                return [
                    'type'    => 'sla',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterUserContactPhone':
                return [
                    'type'    => 'person_contact_phone',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterUserContactAddress':
                return [
                    'type'    => 'person_contact_address',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterUserContactIm':
                return [
                    'type'    => 'person_contact_im',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgContactPhone':
                return [
                    'type'    => 'org_contact_phone',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgContactAddress':
                return [
                    'type'    => 'org_contact_address',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgContactIm':
                return [
                    'type'    => 'org_contact_im',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgGroups':
                return [
                    'type'    => 'org_usergroup',
                    'op'      => $term->getTermOperator(),
                    'options' => ['usergroup' => $options['group_ids']],
                ];

            case 'FilterDateCreated':
                return [
                    'type'    => 'date_created',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterDateResolved':
                return [
                    'type'    => 'date_resolved',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterDateArchived':
                return [
                    'type'    => 'date_archived',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterDateLastAgentReply':
                return [
                    'type'    => 'date_last_agent_reply',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterDateLastUserReply':
                return [
                    'type'    => 'date_last_user_reply',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterUserDateCreated':
                return [
                    'type'    => 'person_date_created',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterCreationSystem':
                return [
                    'type'    => 'creation_system',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgName':
                return [
                    'type'    => 'org_name',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterOrgDateCreated':
                return [
                    'type'    => 'org_date_created',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterUserWaiting':
                $t = $term->getTermOptions();
                $t = $t['time'];

                return [
                    'type'    => 'user_waiting',
                    'op'      => $term->getTermOperator(),
                    'options' => [
                        'waiting_time'      => $t[0],
                        'waiting_time_unit' => $t[1],
                    ],
                ];

            case 'FilterTotalUserWaiting':
                $t = $term->getTermOptions();
                $t = $t['time'];

                return [
                    'type'    => 'total_user_waiting',
                    'op'      => $term->getTermOperator(),
                    'options' => [
                        'waiting_time'      => $t[0],
                        'waiting_time_unit' => $t[1],
                    ],
                ];

            case 'FilterUserName':
                return [
                    'type'    => 'person_name',
                    'op'      => $term->getTermOperator(),
                    'options' => [
                        'name' => $options['name'],
                    ],
                ];

            case 'FilterUserIsManager':
                return [
                    'type'    => 'person_organization_manager',
                    'op'      => $term->getTermOperator(),
                    'options' => [
                        'is_manager' => $options['is_manager'],
                    ],
                ];

            case 'FilterUserIsDisabled':
                return [
                    'type'    => 'person_is_disabled',
                    'op'      => $term->getTermOperator(),
                    'options' => [
                        'is_disabled' => $options['is_disabled'],
                    ],
                ];

            case 'FilterFeedbackLinks':
                return [
                    'type'    => 'feedback_links',
                    'op'      => $term->getTermOperator(),
                    'options' => $options->all(),
                ];

            case 'FilterTicketField':
                return $this->filterFieldToLegacyOptions($term, 'ticket');

            case 'FilterUserField':
                return $this->filterFieldToLegacyOptions($term, 'person');

            case 'FilterOrgField':
                return $this->filterFieldToLegacyOptions($term, 'org');
        }

        return $legacy_terms;
    }

    /**
     * Convert legacy-style terms array into FilterTerms.
     *
     * @param array $legacy_terms
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterTerms Returns an array of replacement terms (usually only one, but possibly multiple if there is a non-exact match)
     */
    public function toFilterTerms(array $legacy_terms)
    {
        $terms = new FilterTerms();

        foreach ($legacy_terms as $term) {
            $t = $this->_legacyTermToFilterTerm($term);

            if (!$t) {
                throw new \InvalidArgumentException('Legacy term has no mapping to new term: '.@$term['type']);
            }

            $terms->addTerm($t);
        }

        return $terms;
    }

    /**
     * @param array $legacy_term
     *
     * @return FilterTermInterface
     */
    private function _legacyTermToFilterTerm(array $legacy_term)
    {
        if (empty($legacy_term['op'])) {
            return;
        }

        $op      = $legacy_term['op'];
        $options = $legacy_term['options'];

        if ($options instanceof OptionsArray) {
            $options = $options->all();
        }

        $type_name = $legacy_term['type'];
        $type_id   = null;
        if (preg_match('#^(.*?)\[(\d+)\]$#', $type_name, $m)) {
            $type_name = $m[1];
            $type_id   = $m[2];
        }

        switch ($type_name) {
            case 'subject':
                return new Terms\FilterSubject($op, [
                    'subject' => @$options['subject'] ?: '',
                ]);

            case 'department':
                $ids = @$options['department'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterDepartment($op, [
                    'department_ids' => $ids,
                ]);

            case 'agent':
                $ids = @$options['agent'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterAgent($op, [
                    'agent_ids' => $ids,
                ]);

            case 'agent_team':
                $ids = @$options['agent_team'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterAgentTeam($op, [
                    'team_ids' => $ids,
                ]);

            case 'participant':
                $ids = @$options['agent'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterAgentParticipant($op, [
                    'agent_ids' => $ids,
                ]);

            case 'label':
                if (isset($options['labels'])) {
                    $labels = $options['labels'];
                } elseif (isset($options['label'])) {
                    $labels = $options['label'];
                } else {
                    $labels = [];
                }

                if (!is_array($labels)) {
                    $labels = [$labels];
                }

                return new Terms\FilterLabels($op, [
                    'labels' => $labels,
                ]);

            case 'status':
                $status = @$options['status'] ?: [];
                if (!is_array($status)) {
                    $status = [$status];
                }

                return new Terms\FilterStatus($op, [
                    'status' => $status,
                ]);

            case 'is_hold':
                return new Terms\FilterHoldStatus($op, [
                    'is_hold' => (bool) ($options['is_hold'] ?: false),
                ]);

            case 'organization':
                $ids = @$options['organization'] ?: [];
                if (!is_array($ids)) {
                    $ids = explode(',', $ids);
                }

                return new Terms\FilterOrgId($op, [
                    'id' => $ids,
                ]);

            case 'product':
                $ids = @$options['product'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterProduct($op, [
                    'product_ids' => $ids,
                ]);

            case 'category':
                $ids = @$options['category'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterCategory($op, [
                    'category_ids' => $ids,
                ]);

            case 'urgency':
                $urgency = @$options['num'] ?: 0;

                return new Terms\FilterUrgency($op, [
                    'urgency' => $urgency,
                ]);

            case 'priority':
                $ids = @$options['priority'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterPriority($op, [
                    'priority_ids' => $ids,
                ]);

            case 'workflow':
                $ids = @$options['workflow'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterWorkflow($op, [
                    'workflow_ids' => $ids,
                ]);

            case 'email_account':
                $ids = @$options['email_account_ids'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterEmailAccount($op, [
                    'email_account_ids' => $ids,
                ]);

            case 'language':
                $ids = @$options['language'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterLanguage($op, [
                    'language_ids' => $ids,
                ]);

            case 'sla':
                return new Terms\FilterSla($op, ['sla_id' => @$options['sla_id'] ?: []]);

            case 'sla_status':
                return new Terms\FilterSlaStatus($op, [
                    'sla_id'     => @$options['sla_id'] ?: 0,
                    'sla_status' => @$options['sla_status'] ?: '',
                ]);

            case 'user_waiting':
                return new Terms\FilterUserWaiting($op, [
                    'time' => [
                        @$options['waiting_time'] ?: 1,
                        @$options['waiting_time_unit'] ?: 'days',
                    ],
                ]);

            case 'total_user_waiting':
                return new Terms\FilterTotalUserWaiting($op, [
                    'time' => [
                        @$options['waiting_time'] ?: 1,
                        @$options['waiting_time_unit'] ?: 'days',
                    ],
                ]);

            case 'date_created':
                return new Terms\FilterDateCreated($op, $options);

            case 'date_resolved':
                return new Terms\FilterDateResolved($op, $options);

            case 'date_archived':
                return new Terms\FilterDateArchived($op, $options);

            case 'date_last_agent_reply':
                return new Terms\FilterDateLastAgentReply($op, $options);

            case 'date_last_user_reply':
                return new Terms\FilterDateLastUserReply($op, $options);

            case 'creation_system':
                return new Terms\FilterCreationSystem($op, ['creation_system' => @$options['creation_system']]);

            case 'person_name':
                return new Terms\FilterUserName($op, [
                    'name' => @$options['name'] ?: '',
                ]);

            case 'person_email':
                return new Terms\FilterUserEmailAddress($op, [
                    'email' => @$options['email'] ?: '',
                ]);

            case 'person_email_domain':
                return new Terms\FilterUserEmailAddress($op, [
                    'email' => '@'.(@$options['email_domain'] ?: ''),
                ]);

            case 'person_organization':
                $ids = @$options['organization'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterOrgId($op, [
                    'org_ids' => $ids,
                ]);

            case 'person_usergroup':
                $ids = @$options['usergroup'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterUserGroups($op, [
                    'group_ids' => $ids,
                ]);

            case 'person_username':
                return new Terms\FilterUserName($op, [
                    'name' => @$options['name'] ?: '',
                ]);

            case 'person_id':
                $ids = @$options['person'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterUserId($op, [
                    'user_ids' => $ids,
                ]);

            case 'person_language':
                $ids = @$options['language'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterUserLanguage($op, [
                    'language_ids' => $ids,
                ]);

            case 'person_date_created':
                return new Terms\FilterUserDateCreated($op, $options);

            case 'person_label':
                $labels = @$options['labels'] ?: [];
                if (!is_array($labels)) {
                    $labels = [$labels];
                }

                return new Terms\FilterUserLabels($op, [
                    'labels' => $labels,
                ]);

            case 'person_contact_phone':
                return new Terms\FilterUserContactPhone($op, $options);

            case 'person_contact_address':
                return new Terms\FilterUserContactAddress($op, $options);

            case 'person_contact_im':
                return new Terms\FilterUserContactIm($op, $options);

            case 'person_is_disabled':
                return new Terms\FilterUserIsDisabled($op, $options);

            case 'feedback_links':
                return new Terms\FilterFeedbackLinks($op, $options);

            case 'person_organization_manager':
                return new Terms\FilterUserIsManager($op, $options);

            case 'org_name':
                return new Terms\FilterOrgName($op, $options);

            case 'org_date_created':
                return new Terms\FilterOrgDateCreated($op, $options);

            case 'org_email_domain':
                return new Terms\FilterOrgEmailDomain($op, [
                    'domain' => @$options['email_domain'] ?: '',
                ]);

            case 'org_label':
                $labels = @$options['labels'] ?: [];
                if (!is_array($labels)) {
                    $labels = [$labels];
                }

                return new Terms\FilterOrgLabels($op, [
                    'labels' => $labels,
                ]);

            case 'org_contact_phone':
                return new Terms\FilterOrgContactPhone($op, $options);

            case 'org_contact_address':
                return new Terms\FilterOrgContactAddress($op, $options);

            case 'org_contact_im':
                return new Terms\FilterOrgContactIm($op, $options);

            case 'org_usergroup':
                $ids = @$options['usergroup'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterOrgGroups($op, [
                    'group_ids' => $ids,
                ]);

            case 'ticket_field':
                $new_opts = $this->legacyFieldToFilterOptions($type_id, $options);

                return new Terms\FilterTicketField($op, $new_opts);

            case 'person_field':
                $new_opts = $this->legacyFieldToFilterOptions($type_id, $options);

                return new Terms\FilterUserField($op, $new_opts);

            case 'org_field':
                $new_opts = $this->legacyFieldToFilterOptions($type_id, $options);

                return new Terms\FilterOrgField($op, $new_opts);

            case 'feedback_rating':
                return new Terms\FilterFeedbackRating($op, $options);

            case 'brand':
                $ids = @$options['brand'] ?: [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }

                return new Terms\FilterBrand($op, ['brand_ids' => $ids]);
        }

        return;
    }

    protected function legacyFieldToFilterOptions($type_id, $options)
    {
        if (isset($options['date1']) || isset($options['date2']) || isset($options['date1_relative']) || isset($options['date2_relative'])) {
            $new_opts             = $options;
            $new_opts['field_id'] = $type_id;
        } elseif (!empty($type_id)) {
            $new_opts             = [];
            $new_opts['field_id'] = $type_id;
            $new_opts['value']    = @$options['custom_fields']['field_'.$type_id];
        } else {
            $new_opts          = [];
            $new_opts['value'] = @$options['value'];
            $new_opts['field'] = @$options['field'];
        }

        return $new_opts;
    }

    protected function filterFieldToLegacyOptions($term, $type)
    {
        /** @var OptionsArray $options */
        $options = $term->getTermOptions();

        if ($options->has('date1') || $options->has('date2') || $options->has('date1_relative') || $options->has('date2_relative')) {
            $fid = $options->get('field_id');

            return [
                'type'    => "{$type}_field[{$fid}]",
                'op'      => $term->getTermOperator(),
                'options' => $options->all(),
            ];
        } elseif ($options->has('field_id')) {
            $fid = $options->get('field_id');

            return [
                'type'    => "{$type}_field[{$fid}]",
                'op'      => $term->getTermOperator(),
                'options' => [
                    'custom_fields' => [
                        'field_'.$fid => $options->get('value'),
                    ],
                ],
            ];
        } else {
            return [
                'type'    => "{$type}_field",
                'op'      => $term->getTermOperator(),
                'options' => [
                    'field' => $options->get('field'),
                    'value' => $options->get('value'),
                ],
            ];
        }
    }
}
