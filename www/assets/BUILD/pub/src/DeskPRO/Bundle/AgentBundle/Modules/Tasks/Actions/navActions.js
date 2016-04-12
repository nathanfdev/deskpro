import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import Immutable from 'immutable';
import { addToCollection, setCollection, releaseCollection, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => (dispatch) => api.sendPost('DP_API/task_projects', data).success((response) => {
    const project = Immutable.fromJS(response.data);
    dispatch(addToCollection('Project', 'all', Immutable.List([project])));
  })
);

export const editProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (projectId, data) => api.sendPut(`DP_API/task_projects/${projectId}`, data)
);

export const deleteProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (projectId) => (dispatch, getState) => api.sendDelete(`DP_API/task_projects/${projectId}`).success(() => {
    let projects = collectionSelectorFactory('Project', 'all')(getState());
    projects = projects.delete(projectId);
    dispatch(releaseCollection('Project', 'all'));
    dispatch(setCollection('Project', 'all', projects));
  })
);

export const initialLoad = createAction(
  'TASKS_NAV_INITIAL_LOAD',
  () => new Promise(resolve => {
    const batch = 'DP_API/batch'
      + '?get[groups]=DP_API/tasks/group_counts'
      + '&get[agents]=DP_API/tasks/agent_counts'
      + '&get[projects]=DP_API/tasks/project_counts'
    ;

    api.sendGet(batch).success(({ responses }) => {
      const payload = flattenBatchResponses(responses);
      resolve(payload);
    });
  })
);
