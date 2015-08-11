import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export function loadAllSlas() {
    DpApi.sendGet('DP_API/slas');
}

export function loadSla(sla_id) {
    DpApi.sendGet(`DP_API/slas/${sla_id}`);
}
