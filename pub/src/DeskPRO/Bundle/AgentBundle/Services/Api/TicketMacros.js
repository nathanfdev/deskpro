import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export function loadAllTicketMacros() {
    return DpApi.sendGet('DP_API/ticket_macros');
}

export function loadTicketMacro(macro_id) {
    return DpApi.sendGet(`DP_API/ticket_macros/${macro_id}`);
}

export function loadUserTicketMacros($user_id) {
    return DpApi.sendGet(`DP_API/users/${user_id}/ticket_macros`)
}

export function loadMyTicketMacros() {
    return loadUserTicketMacros('me');
}
