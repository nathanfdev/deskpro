import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import Immutable from 'immutable';
import { addToCollection, setCollection, releaseCollection, collectionSelectorFactory, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => (dispatch) => api.sendPost('DP_API/task_projects', data).success((response) => {
    const project = Immutable.fromJS(response.data);
    dispatch(addToCollection('Project', 'all', Immutable.List([project])));
  })
);

let updateRequests = 0;
export const editProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (id, data) => (dispatch, getState) => {
    let projects = collectionSelectorFactory('Project', 'all')(getState());

    const oldProject = projects.get(id);
    let newProject = projects.get(id);

    newProject = newProject.withMutations(map => {
      for (const key of Object.keys(data)) {
        const value = Immutable.fromJS(data[key]);
        map.set(key, value);
      }
    });

    if (Immutable.is(newProject, oldProject)) {
      return;
    }

    const promise = api.sendPut(`DP_API/task_projects/${id}`, data);
    updateRequests++;

    // todo show errors (alert?)
    promise.success(() => {
      if (--updateRequests > 0) return;
      projects = collectionSelectorFactory('Project', 'all')(getState()).set(id, newProject);
      dispatch(setCollection('Project', 'all', projects));
    }).error(() => {
      if (--updateRequests > 0) return;
      projects = collectionSelectorFactory('Project', 'all')(getState()).set(id, oldProject);
      dispatch(setCollection('Project', 'all', projects));
    });
  }
);

export const deleteProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (projectId) => (dispatch, getState) => api.sendDelete(`DP_API/task_projects/${projectId}`).success(() => {
    dispatch(removeFromCollection('Project', 'all', [projectId]));
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
