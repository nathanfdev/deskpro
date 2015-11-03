import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

const getRemainingCount = filter =>
  Tasks.loadTasksRemainingCount(filter).then(
      result => result.getData()
  );

export const loadAllTasksRemainingCount = createAction(
  'TASKS_LOAD_ALL_REMAINING_COUNT',
  () => getRemainingCount()
);

export const loadMyTasksRemainingCount = createAction(
  'TASKS_LOAD_MY_REMAINING_COUNT',
  () => getRemainingCount({
    assigned: 'me'
  })
);

export const loadTeamTasksRemainingCount = createAction(
  'TASKS_LOAD_TEAM_REMAINING_COUNT',
  () => getRemainingCount({
    assigned_team: 'me'
  })
);

export const loadDepartmentTasksRemainingCount = createAction(
  'TASKS_LOAD_DEPARTMENT_REMAINING_COUNT',
  () => getRemainingCount({
    assigned_department: 'me'
  })
);

export const loadDelegatedTasksRemainingCount = createAction(
  'TASKS_LOAD_DELEGATED_REMAINING_COUNT',
  () => getRemainingCount({
    assigned: 'not_me',
    creator: 'me'
  })
);

export const loadUnassignedTasksRemainingCount = createAction(
  'TASKS_LOAD_UNASSIGNED_REMAINING_COUNT',
  () => getRemainingCount({
    assigned: null,
    assigned_team: null,
    assigned_department: null
  })
);
