import { api } from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export function loadAllStarCounts() {
  return api.sendGet('DP_API/ticket_stars/all/counts');
}

export function loadTicketsForStar(star_name) {
  return api.sendGet(`DP_API/ticket_stars/${star_name}/tickets`);
}
