import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

/**
 * Load all departments.
 * @param {Object} options - options to get teams
 * @return {object} - promise
 */
export function loadAgentTeams(options = {}) {
  return api.sendGet('DP_API/agent_teams?' + compileParams(options));
}

export function loadAll() {
  return loadAgentTeams();
}

export function loadAgentTeam(teamId) {
  return api.sendGet(`DP_API/agent_teams/${teamId}`);
}

export function loadAgentTeamAgents(teamId) {
  return api.sendGet(`DP_API/agent_teams/${teamId}/agents`);
}

/**
 * Compile parameters into a URL string
 * @param {Object} params - parameters to be compiled
 * @returns {string} - compiled string
 */
function compileParams(params) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}
