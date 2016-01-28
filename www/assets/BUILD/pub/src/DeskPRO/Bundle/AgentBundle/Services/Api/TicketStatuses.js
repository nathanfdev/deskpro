import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export function loadAllTicketStatuses() {
    DpApi.sendGet('DP_API/ticket_statuses');
}

export function loadTicketStatusesForDepartment($department_id) {
    DpApi.sendGet(`DP_API/ticket_departments/${department_id}/ticket_statuses`);
}
