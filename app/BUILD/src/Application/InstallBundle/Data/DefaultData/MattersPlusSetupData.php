<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\CustomDefTicket;

class MattersPlusSetupData extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     */
    public function runInstall()
    {
        $this->installLang();
        $this->installFields();
        $this->installLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function runSync()
    {
        $this->runInstall();
    }

    private function installLang()
    {
        $langFile = require __DIR__.'/../../../../../languages/default/agent.php';

        $replacements = [
            'Awaiting Agent' => 'Open',
            'Awaiting User'  => 'Open',
            'Resolved'       => 'Closed',
            'awaiting agent' => 'open',
            'awaiting user'  => 'open',
            'resolved'       => 'closed',
            'Ticket'         => 'Matter',
            'ticket'         => 'matter',
            'Department'     => 'DEP',
            'Organization'   => 'Department',
            'organization'   => 'department',
            'Agent'          => 'Solicitor',
            'agent'          => 'solicitor',
        ];

        $find = array_keys($replacements);
        $repl = array_values($replacements);

        $updateLang = [];
        $clearIds   = [];

        foreach ($langFile as $phraseId => $phrase) {
            $m         = 0;
            $varTokens = [];
            if (preg_match_all('#\{\{.*?\}\}#', $phrase, $m)) {
                foreach ($m[0] as $varName) {
                    $t             = '%'.uniqid('tok', true).'%';
                    $varTokens[$t] = $varName;
                    $phrase        = str_replace($varName, $t, $phrase);
                }
            }

            $now = date('Y-m-d H:i:s');

            $newPhrase = str_replace($find, $repl, $phrase);
            if ($newPhrase !== $phrase) {
                if ($varTokens) {
                    $newPhrase = str_replace(array_keys($varTokens), array_values($varTokens), $newPhrase);
                }

                $clearIds[] = $phraseId;
                $groupName  = explode('.', $phraseId);
                array_pop($groupName);
                $groupName    = implode('.', $groupName);
                $updateLang[] = ['language_id' => 1, 'name' => $phraseId, 'phrase' => $newPhrase, 'groupname' => $groupName, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        $this->getDb()->deleteIn('phrases', $clearIds, 'name', false, 'language_id = 1');
        $this->getDb()->batchInsert('phrases', $updateLang);
    }

    private function installLayout()
    {
        $layoutFields = [
            // Standard fields here
            $this->makeLayoutField('person'),
            $this->makeLayoutField('department'),
            $this->makeLayoutField('subject'),
            $this->makeLayoutField('attachments'),
            $this->makeLayoutField('message'),

            $this->makeLayoutFieldAlias('matter_type'),
            $this->makeLayoutFieldAlias('matter_description'),
            $this->makeLayoutFieldAlias('target_complete_date'),
            $this->makeLayoutFieldAlias('deal_value'),
            $this->makeLayoutFieldAlias('doc_link'),
            $this->makeLayoutFieldAlias('is_outsourced'),
            $this->makeLayoutFieldAlias('is_recharge_client', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('external_lawyer', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('cost_type', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('approved_spend', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('is_counsel_fee', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('counsel_fee_type', ['showOnToggle' => 'is_counsel_fee']),
            $this->makeLayoutFieldAlias('counsel_approved_spend', ['showOnToggle' => 'is_counsel_fee']),
            $this->makeLayoutFieldAlias('cost_variance', ['showOnToggle' => 'is_outsourced']),
            $this->makeLayoutFieldAlias('counsel_cost_variance', [['showOnToggle' => 'is_outsourced'], ['showOnToggle' => 'is_counsel_fee']]),
            $this->makeLayoutFieldAlias('matter_close_status'),
            $this->makeLayoutFieldAlias('closing_details', ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]),
            $this->makeLayoutFieldAlias('contract_state_date', ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]),
            $this->makeLayoutFieldAlias('has_break', ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]),
            $this->makeLayoutFieldAlias('contract_break_date', [['showOnToggle' => 'has_break'], ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]]),
            $this->makeLayoutFieldAlias('break_notice_date', [['showOnToggle' => 'has_break'], ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]]),
            $this->makeLayoutFieldAlias('contract_expire_date', ['showOnOption' => ['alias' => 'matter_close_status', 'value' => 'Matter Complete']]),
        ];

        $layout = [
            '@CLASS' => 'Application\\DeskPRO\\TicketLayout\\Layout',
            '@DATA'  => [
                'version' => 1,
                'fields'  => $layoutFields,
            ],
        ];

        $layoutJson = json_encode($layout, \JSON_PRETTY_PRINT);

        $this->getDb()->executeUpdate('DELETE FROM ticket_layouts WHERE department_id IS NULL');
        $this->getDb()->insert('ticket_layouts', [
            'is_enabled'    => true,
            'department_id' => null,
            'user_layout'   => $layoutJson,
            'agent_layout'  => $layoutJson,
            'date_updated'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function installFields()
    {
        $this->makeField('matter_type', 'Matter Type *', 'choice', ['required' => true], [
            'Corporate',
            'Commercial Contract',
            'Property',
            'Employment Law',
            'Co Sec',
            'Litigation',
        ]);
        $this->makeField('matter_description', 'Matter Description *', 'textarea', ['required' => true]);
        $this->makeField('target_complete_date', 'Target Completion Date', 'date');
        $this->makeField('deal_value', 'Deal Value / Claim Value', 'text');
        $this->makeField('doc_link', 'Document Link', 'text');
        $this->makeField('is_recharge_client', 'Recharge Client?', 'toggle');
        $this->makeField('is_outsourced', 'Matter Outsourced?', 'toggle');
        $this->makeField('external_lawyer', 'External Lawyer', 'choice', [], ['Samlpe Value']);
        $this->makeField('cost_type', 'Cost Type', 'choice', [], ['Fixed', 'Estimate']);
        $this->makeField('approved_spend', 'Total Approved Spend', 'text');
        $this->makeField('is_counsel_fee', 'Counsel Fee', 'toggle');
        $this->makeField('counsel_fee_type', 'Cost Type', 'choice', [], ['Fixed', 'Estimate']);
        $this->makeField('counsel_approved_spend', 'Total Approved Counsel Spend', 'text');
        $this->makeField('closing_details', 'Closing Details', 'textarea');
        $this->makeField('cost_variance', 'Cost Variance', 'text');
        $this->makeField('counsel_cost_variance', 'Counsel Cost Variance', 'text');
        $this->makeField('matter_close_status', 'Matter Close Status', 'choice', [], [
            'Matter Complete',
            'Matter Aborted',
        ]);
        $this->makeField('contract_state_date', 'Contract Start Date', 'date');
        $this->makeField('contract_break_date', 'Contract Break Date', 'date');
        $this->makeField('contract_expire_date', 'Contract Expiry Date', 'date');
        $this->makeField('break_notice_date', 'Serve Break Notice by Date', 'date');
        $this->makeField('has_break', 'Has Break?', 'toggle');
    }

    /**
     * @param string $alias
     * @param string $title
     * @param string $type
     * @param array  $options
     * @param array  $choices
     *
     * @return CustomDefTicket
     */
    private function makeField($alias, $title, $type, $options = [], $choices = [])
    {
        try {
            $field = $this->findFieldByAlias($alias);
            $isNew = false;
        } catch (\Exception $e) {
            $field = new CustomDefTicket();
            $isNew = true;
        }

        $realOptions = [];
        if (@$options['required']) {
            $realOptions['agent_validation_type'] = 'required';
            $realOptions['validation_type']       = 'required';
            $realOptions['required']              = 'required';
            $realOptions['agent_required']        = '1';
            $realOptions['agent_min_length']      = '1';
            $realOptions['agent_min_length']      = '1';
        }

        $field->setTitle($title);
        $field->setWidgetType($type);
        $field->setOptions($options);

        $this->getEm()->persist($field);

        if (!empty($choices)) {
            foreach ($choices as $idx => $choiceTitle) {
                if (!count($field->getChildren()->filter(function ($c) use ($choiceTitle) {
                    return $c->getTitle() === $choiceTitle;
                }))) {
                    $c = new CustomDefTicket();
                    $c->setTitle($choiceTitle);
                    $c->setDisplayOreder($idx);
                    $c->setParent($field);
                    $this->getEm()->persist($c);
                    $field->addChild($c);
                }
            }
        }

        $this->getEm()->flush();

        if ($isNew) {
            $this->getDb()->insert('object_aliases', [
                'custom_def_ticket_id' => $field->getId(),
                'alias'                => $alias,
                'object_type'          => 'custom_def_ticket',
            ]);
        }

        return $field;
    }

    /**
     * @param string string $alias
     * @param string string $childTitle
     *
     * @return CustomDefTicket
     */
    private function findFieldByAlias($alias, $childTitle = null)
    {
        $existFieldId = $this->getDb()->fetchColumn('
            SELECT custom_def_ticket_id
            FROM object_aliases
            WHERE alias = ?
        ', [$alias]);

        if (!$existFieldId) {
            throw new \InvalidArgumentException("No field with alias: $alias");
        }

        $field = $this->getEm()->find(CustomDefTicket::class, $existFieldId);

        if ($childTitle) {
            $child = $field->getChildren()->filter(function ($c) use ($childTitle) {
                return $c->getTitle() === $childTitle;
            })->first();
            if (!$child) {
                throw new \InvalidArgumentException();
            }

            return $child;
        }

        return $field;
    }

    /**
     * @param string     $alias
     * @param array|null $showCond
     *
     * @return array
     */
    private function makeLayoutFieldAlias($alias, $showCond = null)
    {
        return $this->makeLayoutField($this->findFieldByAlias($alias), $showCond);
    }

    /**
     * @param string|CustomDefTicket $field
     * @param array|null             $showCond
     *
     * @return array
     */
    private function makeLayoutField($field, $showCond = null)
    {
        if ($field instanceof CustomDefTicket) {
            $fieldType = 'ticket_field';
            $fieldId   = $field->getId();
        } else {
            $fieldType = $field;
            $fieldId   = null;
        }

        if ($showCond) {
            $criteria = [
                'version' => 1,
                'mode'    => 'all',
                'terms'   => [],
            ];

            if (empty($showCond[0])) {
                $showCond = [$showCond];
            }

            foreach ($showCond as $cond) {
                if (!empty($cond['showOnToggle'])) {
                    $criteria['terms'][] = $this->makeToggleShowCond($cond['showOnToggle']);
                } elseif (!empty($cond['showOnOption'])) {
                    $criteria['terms'][] = $this->makeChoiceShowCond($cond['showOnOption']['alias'], $cond['showOnOption']['value']);
                } else {
                    throw new \Exception('unknown cond');
                }
            }
        } else {
            $criteria = null;
        }

        return [
            'version'    => 1,
            'field_type' => $fieldType,
            'field_id'   => $fieldId,
            'options'    => [
                'criteria'           => null,
                'on_newticket'       => true,
                'on_viewticket'      => true,
                'on_viewticket_mode' => 'always',
                'on_editticket'      => true,
                'criteria'           => $criteria,
            ],
        ];
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    private function makeToggleShowCond($alias)
    {
        $field   = $this->findFieldByAlias($alias);
        $type    = "CheckTicketField{$field->getId()}";
        $op      = 'isset';
        $options = [
            'value'     => 1,
            'type_name' => 'toggle',
            'field_id'  => $field->getId(),
        ];

        return [
            'type'    => $type,
            'op'      => $op,
            'options' => $options,
        ];
    }

    /**
     * @param string $alias
     * @param string $$value
     *
     * @return array
     */
    private function makeChoiceShowCond($alias, $value)
    {
        $field        = $this->findFieldByAlias($alias);
        $choiceOption = $this->findFieldByAlias($alias, $value);

        $type    = "CheckTicketField{$field->getId()}";
        $op      = 'is';
        $options = [
            'value'     => [$choiceOption->getId()],
            'type_name' => 'choice',
            'field_id'  => $field->getId(),
        ];

        return [
            'type'    => $type,
            'op'      => $op,
            'options' => $options,
        ];
    }
}
