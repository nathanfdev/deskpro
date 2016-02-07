import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export function loadAllStarCounts() {
  return api.sendGet('DP_API/ticket_stars/all/counts');
}

export function loadTicketsForStar(star_name) {
  return api.sendGet(`DP_API/ticket_stars/${star_name}/tickets`);
}
