import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const createProject = createAction(
  'TASKS_POST_PROJECT',
  data => {
    return Tasks.createProject(data).then(
      value => value,
      value => value.xhr.responseJSON
    );
  }
);

export const editProject = createAction(
  'TASKS_EDIT_PROJECT',
  (projectId, data) => {
    return Tasks.editProject(projectId, data).then(
      value => value,
      value => value.xhr.responseJSON
    );
  }
);
