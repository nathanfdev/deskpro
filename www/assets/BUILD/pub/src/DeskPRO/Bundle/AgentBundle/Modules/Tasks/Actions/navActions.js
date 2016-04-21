import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import Immutable from 'immutable';
import { addToCollection, collectionSelectorFactory, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const createProject = createAction(
  'TASKS_NAV_POST_PROJECT',
  data => (dispatch) => api.sendPost('DP_API/task_projects', data).success((response) => {
    const project = Immutable.fromJS(response.data);
    dispatch(addToCollection('Project', 'all', Immutable.List([project])));
  })
);

export const getProject = createAction(
  'TASKS_LIST_GET_PROJECT',
  (id) => dispatch => api.sendGet(`DP_API/task_projects/${id}`).success(response => {
    if (!response.data) return;
    const project = Immutable.fromJS(response.data);
    dispatch(addToCollection('Project', 'all', Immutable.List([project])));
  })
);

const updates = {};
export const editProject = createAction(
  'TASKS_NAV_EDIT_PROJECT',
  (id, data) => (dispatch, getState) => {
    const projects = collectionSelectorFactory('Project', 'all')(getState());
    const oldProject = projects.get(id);
    const promise = api.sendPut(`DP_API/task_projects/${id}`, data);
    updates[id] = promise;

    // todo show errors (alert?)
    promise.success(() => {
      if (updates[id] !== promise) return;
      delete updates[id];
      dispatch(getProject(id));
    }).error(() => {
      if (updates[id] !== promise) return;
      delete updates[id];
      dispatch(addToCollection('Project', 'all', Immutable.List([oldProject])));
    });
  }
);

export const deleteProject = createAction(
  'TASKS_NAV_DELETE_PROJECT',
  (projectId) => (dispatch) => api.sendDelete(`DP_API/task_projects/${projectId}`).success(() => {
    dispatch(removeFromCollection('Project', 'all', [projectId]));
  })
);

export const initialLoad = createAction(
  'TASKS_NAV_INITIAL_LOAD',
  () => new Promise(resolve => {
    const batch = 'DP_API/batch'
            + '?get[groups]=DP_API/tasks/counts/groups'
            + '&get[agents]=DP_API/tasks/counts/agents'
            + '&get[projects]=DP_API/tasks/counts/projects'
      ;

    api.sendGet(batch).success(({ responses }) => {
      const payload = flattenBatchResponses(responses);
      resolve(payload);
    });
  })
);
