import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => api.sendPost('DP_API/projects', data)
);

export const editProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (projectId, data) => api.sendPut('DP_API/projects/' + projectId, data)
);

export const initialLoad = createAction(
  'TASKS_NAV_INITIAL_LOAD',
  () => new Promise(resolve => {
    const batch = 'DP_API/batch'
      + '?get[groups]=DP_API/tasks/group_counts'
      + '&get[agents]=DP_API/tasks/agent_counts'
      + '&get[projects]=DP_API/tasks/project_counts'
    ;

    api.sendGet(batch).success(({responses}) => {
      const payload = flattenBatchResponses(responses);
      resolve(payload);
    });
  })
);
