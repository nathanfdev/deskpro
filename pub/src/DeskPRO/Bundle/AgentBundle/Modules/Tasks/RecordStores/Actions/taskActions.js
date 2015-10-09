import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

// import Immutable from 'immutable';

export const releaseProjects = createAction('RELEASE_PROJECTS', recordStoreActions.releaseRecords());
export const releaseProjectRequest = createAction('RELEASE_PROJECT_REQUEST', recordStoreActions.releaseRequest());
export const setProjectRequest = createAction('SET_PROJECTS_REQUEST', recordStoreActions.setRequestRecords());

export const loadProjects = createAction(
  'LOAD_PROJECTS',
  recordStoreActions.requestRecords(['RecordStores', 'Tasks', 'projects'], (missingIds) => {
    return new Promise((resolve, reject) => {
      const recs = [
        {id: 1, title: 'Test title'},
        {id: 2, title: 'Another test'}
      ];

      resolve(recs);
    });

  // return Tasks.loadProjects({is_done: false}).then(
  //   value => {
  //     mapKeyedFromArray(value.getData());
  //   }
  // );
  })
);

// export const loadTasks = createAction('LOAD_TASKS', recordStoreActions.requestRecords(['Tasks', 'tasks'], (missingIds) => {
//   return new Promise((resolve, reject) => {
//     const recordMap = mapKeyedFromArray(recs, 'id');
//     resolve(recordMap);
//   });
// }));







// export const releaseWidgets       = createAction('RELEASE_WIDGETS',        recordStoreActions.releaseRecords());
// export const releaseWidgetRequest = createAction('RELEASE_WIDGET_REQUEST', recordStoreActions.releaseRequest());
// export const setWidgetRequest     = createAction('SET_WIDGETS_REQUEST',    recordStoreActions.setRequestRecords());

// export const loadWidgets = createAction('LOAD_WIDGETS', recordStoreActions.requestRecords(['Widgets', 'widgets'], (missingIds) => {
//   return new Promise((resolve, reject) => {
//     // Normally youd load records here (e.g., via api)
//     // but let's just imagine...
//     const recs = [
//       {id: 1, title: "Foo" },
//       {id: 2, title: "Bar" }
//     ]

//     // record-store expects a map. Keys are IDs.
//     // Use the helper function mapKeyedFromArray to make this easy
//     const recordMap = mapKeyedFromArray(recs, 'id');

//     resolve(recordMap);
//   });
// }));
