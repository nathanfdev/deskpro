<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0023_ticketlayouts extends AbstractBuild
{
    public function run()
    {
        $this->out('Modify ticket layouts');
        $ticketLayouts = $this->container->getDb()->fetchAll('SELECT id, user_layout, agent_layout FROM ticket_layouts');
        foreach ($ticketLayouts as $l) {
            $this->processTicketLayout($l);
        }
    }

    /**
     * @param array $ticketLayout
     */
    private function processTicketLayout(array $ticketLayout)
    {
        $userLayout  = @json_decode($ticketLayout['user_layout'], true) ?: null;
        $agentLayout = @json_decode($ticketLayout['agent_layout'], true) ?: null;

        $update = [];
        if ($userLayout && !empty($userLayout['@DATA']['fields'])) {
            $update['user_layout'] = json_encode($this->transformLayoutArray($userLayout));
        }
        if ($agentLayout && !empty($agentLayout['@DATA']['fields'])) {
            $update['agent_layout'] = json_encode($this->transformLayoutArray($agentLayout));
        }

        if ($update) {
            $this->container->getDb()->update('ticket_layouts', $update, ['id' => $ticketLayout['id']]);
        }
    }

    /**
     * @param array $layout
     *
     * @return array
     */
    private function transformLayoutArray(array $layout)
    {
        $newFields = [];
        $hasPerson = false;

        foreach ($layout['@DATA']['fields'] as $field) {
            if ($field['field_type'] === 'user_name' || $field['field_type'] === 'user_email') {
                if (!$hasPerson) {
                    $hasPerson   = true;
                    $newFields[] = $this->getPersonFieldDev();
                }
            } elseif ($field['field_type'] === 'attach' || $field['field_type'] === 'attachments') {
                continue;
            } else {
                $newFields[] = $field;

                if ($field['field_type'] === 'message') {
                    $newFields[] = $this->getAttachFieldDev();
                }
            }
        }

        $layout['@DATA']['fields'] = $newFields;

        return $layout;
    }

    /**
     * @return array
     */
    private function getPersonFieldDev()
    {
        return [
            'version'    => 1,
            'field_type' => 'person',
            'field_id'   => null,
            'options'    => [
                'criteria'           => null,
                'on_newticket'       => true,
                'on_viewticket'      => true,
                'on_viewticket_mode' => 'always',
                'on_editticket'      => true,
            ],
        ];
    }

    /**
     * @return array
     */
    private function getAttachFieldDev()
    {
        return [
            'version'    => 1,
            'field_type' => 'attachments',
            'field_id'   => null,
            'options'    => [
                'criteria'           => null,
                'on_newticket'       => true,
                'on_viewticket'      => true,
                'on_viewticket_mode' => 'always',
                'on_editticket'      => true,
            ],
        ];
    }
}

//[[build:1460678410]]
