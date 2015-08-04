import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

function loadAllTicketLayouts() {
    DpApi.sendGet('DP_API/ticket_layouts');
}

function loadTicketLayout(layout_id) {
    DpApi.sendGet(`DP_API/ticket_layouts/${layout_id}`);
}

function loadTicketLayoutsForDepartment($department_id) {
    DpApi.sendGet(`DP_API/departments/${department_id}/ticket_layouts`)
}
