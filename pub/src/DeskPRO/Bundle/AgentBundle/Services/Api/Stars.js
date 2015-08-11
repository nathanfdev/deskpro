import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export function loadAllStarCounts() {
    DpApi.sendGet('DP_API/ticket_stars/all/counts');
}

export function loadTicketsForStar(star_name) {
    DpApi.sendGet(`DP_API/ticket_stars/${star_name}/tickets`);
}
