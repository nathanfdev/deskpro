import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import Immutable from 'immutable';
import { setCollection, releaseCollection, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => (dispatch, getState) => api.sendPost('DP_API/projects', data).success((response) => {
    const project = Immutable.fromJS(response.data);
    let projects = collectionSelectorFactory('Project', 'all')(getState());
    projects = projects.set(project.get('id'), project);
    dispatch(setCollection('Project', 'all', projects));
  })
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
