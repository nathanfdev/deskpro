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
