import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => Tasks.createProject(data)
);

export const editProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (projectId, data) => Tasks.editProject(projectId, data)
);

export const initialLoad = createAction(
  'TASKS_NAV_INITIAL_LOAD',
  () => new Promise(resolve => {
    const batch = 'DP_API/batch'
      + '?get[groups]=DP_API/tasks/group_counts'
      + '&get[agents]=DP_API/tasks/agent_counts'
      + '&get[projects]=DP_API/tasks/project_counts'
    ;

    DpApi.sendGet(batch).success(({responses}) => {
      const payload = flattenBatchResponses(responses);
      resolve(payload);
    });
  })
);
