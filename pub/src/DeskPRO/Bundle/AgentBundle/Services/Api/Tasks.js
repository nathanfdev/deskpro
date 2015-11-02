import DpApi from '../DpApi';

/*
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
export function compileParams(params) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}

/*
 * Load a generic API endpoint. Only use when you need to get the address from the action
 * @param address
 * @param params
 * @return Promise
 */
export function loadAddress(address, params = {}) {
  params.count = 50;

  let url = address;

  if (params !== {}) {
    if (address.indexOf('?') === -1) {
      url = address + '?' + compileParams(params);
    } else {
      url = address + '&' + compileParams(params);
    }
  }

  return DpApi.sendGet('DP_API/' + url);
}

/*
 * Load tasks
 * @param params
 * @return Promise
 */
export function loadTasks(params = {}) {
  return DpApi.sendGet('DP_API/tasks?' + compileParams(params));
}

/*
 * Load the number of remaining tasks
 * @param params
 * @return Promise
 */
export function loadTasksRemainingCount(params = {}) {
  const query = {
    count_only: true,
    is_done: false,
    ...params
  };

  return DpApi.sendGet('DP_API/tasks?' + compileParams(query));
}

/*
 * Load agents
 * @param params
 * @return Promise
 */
export function loadAgents(params = {}) {
  const query = {
    is_agent: 1,
    ...params
  };

  return DpApi.sendGet('DP_API/people?' + compileParams(query));
}

/*
 * Load all projects
 * @param params
 * @return Promise
 */
export function loadProjects(params = {}) {
  return DpApi.sendGet('DP_API/projects?' + compileParams(params));
}

/*
 * Load all labels
 * @param params
 * @return Promise
 */
export function loadLabels(params = {}) {
  const query = {
    group: true,
    ...params
  };

  return DpApi.sendGet('DP_API/task_labels?' + compileParams(query));
}

/*
 * Load all teams
 * @param params
 * @return Promise
 */
export function loadTeams(params = {}) {
  return DpApi.sendGet('DP_API/agent_teams?' + compileParams(params));
}

/*
 * Load all departments
 * @param params
 * @return Promise
 */
export function loadDepartments(params = {}) {
  return DpApi.sendGet('DP_API/ticket_departments?' + compileParams(params));
}

/*
 * Create a project
 * @param data
 * @return Promise
 */
export function createProject(data) {
  return DpApi.sendPost('DP_API/projects', data);
}

/*
 * Update a project
 * @param projectId
 * @param data
 * @return Promise
 */
export function editProject(projectId, data) {
  return DpApi.sendPut('DP_API/projects/' + projectId, data);
}

/*
 * Create a task
 * @param data
 * @return Promise
 */
export function createTask(data) {
  return DpApi.sendPost('DP_API/tasks', data);
}

/*
 * Update a task
 * @param taskId
 * @param data
 * @return Promise
 */
export function editTask(taskId, data) {
  // Clean-up
  // The API supports multiple assignment, but the UI doesn't yet
  if (data.departments && data.departments.length > 0) {
    data.teams = [];
    data.agents = [];
  } else if (data.teams && data.teams.length > 0) {
    data.departments = [];
    data.agents = [];
  } else if (data.agents && data.agents.length > 0 || data.agents === false) {    // Allows un-assign
    data.departments = [];
    data.teams = [];
  }

  return DpApi.sendPut('DP_API/tasks/' + taskId, data);
}

/*
 * Update multiple tasks
 * @param data
 * @return Promise
 */
export function massEditTasks(data) {
  // Clean-up
  // The API supports multiple assignment, but the UI doesn't yet
  if (data.departments && data.departments.length > 0) {
    data.teams = [];
    data.agents = [];
  } else if (data.teams && data.teams.length > 0) {
    data.departments = [];
    data.agents = [];
  } else if (data.agents && data.agents.length > 0 || data.agents === false) {    // Allows un-assign
    data.departments = [];
    data.teams = [];
  }

  return DpApi.sendPut('DP_API/tasks/mass', data);
}

/*
 * Load links for related items for a task
 * @param params
 * @return Promise
 */
export function loadLinks(params = {}) {
  return DpApi.sendGet('DP_API/task_links?' + compileParams(params));
}

export function loadLinkedTickets(params = {}) {
  return DpApi.sendGet('DP_API/tickets?' + compileParams(params));
}

/*
 * List all lists for a project
 * @param projectId
 */
export function loadLists(projectId) {
  return DpApi.sendGet('DP_API/projects/' + projectId + '/lists');
}
