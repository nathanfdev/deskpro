import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const createProject = createAction(
  'TASKS_POST_PROJECT',
  data => Tasks.createProject(data)
);

export const editProject = createAction(
  'TASKS_EDIT_PROJECT',
  (projectId, data) => Tasks.editProject(projectId, data)
);
