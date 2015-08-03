import DpApi from "../DpApi";

/** Load all departments. */
export function loadCustomFields() {
    return DpApi.sendGet('DP_API/custom_fields/');
}

export function loadCustomFieldsForTicket(ticket_id) {
    return DpApi.sendGet(`DP_API/tickets/${ticket_id}/custom_fields`);
}

export function loadSingleCustomFieldForTicket(ticket_id, field_id) {
    return DpApi.sendGet(`DP_API/tickets/${ticket_id}/custom_fields/${field_id}`);
}
