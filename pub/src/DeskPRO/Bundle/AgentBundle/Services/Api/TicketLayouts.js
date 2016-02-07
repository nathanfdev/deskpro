import { api } from "DeskPRO/Bundle/AppBundle/DAL/Http/DpApi";

export function loadAllTicketLayouts() {
    api.sendGet('DP_API/ticket_layouts');
}

export function loadTicketLayout(layout_id) {
    api.sendGet(`DP_API/ticket_layouts/${layout_id}`);
}

export function loadTicketLayoutsForDepartment($department_id) {
    api.sendGet(`DP_API/ticket_departments/${department_id}/ticket_layouts`)
}
