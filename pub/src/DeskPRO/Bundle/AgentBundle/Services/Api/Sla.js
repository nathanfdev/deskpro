import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

function loadAllSlas() {
    DpApi.sendGet('DP_API/slas');
}

function loadSla(sla_id) {
    DpApi.sendGet(`DP_API/slas/${sla_id}`);
}
