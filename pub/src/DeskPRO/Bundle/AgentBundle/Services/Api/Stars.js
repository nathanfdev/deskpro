import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

function loadAllStarCounts() {
    DpApi.sendGet('DP_API/ticket_stars/all/counts');
}

function loadTicketsForStar(star_name) {
    DpApi.sendGet(`DP_API/ticket_stars/${star_name}/tickets`);
}
