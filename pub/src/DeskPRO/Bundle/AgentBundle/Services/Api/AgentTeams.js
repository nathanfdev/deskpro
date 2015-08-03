import DpApi from "../DpApi";

/** Load all departments. */
export function loadAgentTeams() {
    return DpApi.sendGet('DP_API/agent_teams/');
}

export function loadAgentTeam(team_id) {
    return DpApi.sendGet(`DP_API/agent_teams/${team_id}`);
}
