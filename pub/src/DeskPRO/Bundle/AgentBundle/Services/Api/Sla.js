import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export function loadAllSlas() {
  return api.sendGet('DP_API/slas');
}

export function loadSla(sla_id) {
  return api.sendGet(`DP_API/slas/${sla_id}`);
}
