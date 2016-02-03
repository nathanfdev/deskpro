import { api } from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export function loadAllTicketStatuses() {
    api.sendGet('DP_API/ticket_statuses');
}

export function loadTicketStatusesForDepartment($department_id) {
    api.sendGet(`DP_API/ticket_departments/${department_id}/ticket_statuses`);
}
