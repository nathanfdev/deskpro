import DpApi from "../DpApi";

/**
 * Load a generic API endpoint. Only use when you need to get the address from the action
 * @param address
 * @param params
 * @return Promise
 */
export function loadAddress(address, params = {}) {
  if (params.length > 0) {
    address = address + '?' + compileParams(params);
  }

  return DpApi.sendGet('DP_API/' + address);
}

/**
 * Load the number of remaining tasks
 * @param params
 * @return Promise
 */
export function loadTasksRemainingCount(params = {}) {
  let query = {
    count_only: true,
    is_done: false,
    ...params
  };

  return DpApi.sendGet('DP_API/tasks?' + compileParams(query));
}

/**
 * Load agents
 * @param params
 * @return Promise
 */
export function loadAgents(params = {}) {
  let query = {
    is_agent: 1,
    ...params
  };

  return DpApi.sendGet('DP_API/people?' + compileParams(query));
}

/**
 * Load all projects
 * @param params
 * @return Promise
 */
export function loadProjects(params = {}) {
  return DpApi.sendGet('DP_API/projects?' + compileParams(params));
}

/**
 * Load all labels
 * @param params
 * @return Promise
 */
export function loadLabels(params = {}) {
  let query = {
    group: true,
    ...params
  };

  return DpApi.sendGet('DP_API/task_labels?' + compileParams(query));
}

/**
 * Load all teams
 * @param params
 * @return Promise
 */
export function loadTeams(params = {}) {
  return DpApi.sendGet('DP_API/teams?' + compileParams(params));
}

/**
 * Load all departments
 * @param params
 * @return Promise
 */
export function loadDepartments(params = {}) {
  return DpApi.sendGet('DP_API/departments?' + compileParams(params));
}

/**
 * Create a project
 * @param data
 * @return Promise
 */
export function createProject(data) {
  return DpApi.sendPost('DP_API/projects', data);
}

/**
 * Update a project
 * @param projectId
 * @param data
 * @return Promise
 */
export function editProject(projectId, data) {
  return DpApi.sendPut('DP_API/projects/' + projectId, data);
}

/**
 * Create a task
 * @param data
 * @return Promise
 */
export function createTask(data) {
  return DpApi.sendPost('DP_API/tasks', data);
}

/**
 * Update a task
 * @param taskId
 * @param data
 * @return Promise
 */
export function editTask(taskId, data) {
  return DpApi.sendPut('DP_API/tasks/' + taskId, data);
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
