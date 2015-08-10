import DpApi from "../DpApi";

/** Load all departments. */
export function loadAgentTeams(options = {}) {
  return DpApi.sendGet('DP_API/agent_teams?' + compileParams(options));
}

export function loadAgentTeam(team_id) {
  return DpApi.sendGet(`DP_API/agent_teams/${team_id}`);
}

/**
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
function compileParams(params) {
  let compiled = [];

  for (let key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}
