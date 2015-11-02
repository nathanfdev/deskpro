import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export function loadAllTicketLayouts() {
    DpApi.sendGet('DP_API/ticket_layouts');
}

export function loadTicketLayout(layout_id) {
    DpApi.sendGet(`DP_API/ticket_layouts/${layout_id}`);
}

export function loadTicketLayoutsForDepartment($department_id) {
    DpApi.sendGet(`DP_API/ticket_departments/${department_id}/ticket_layouts`)
}
