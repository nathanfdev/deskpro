import { createAction } from 'Ampliflux';
import { pluck } from 'lodash';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as UserGroups from 'DeskPRO/Bundle/AgentBundle/Services/Api/UserGroups';
import * as Organizations from 'DeskPRO/Bundle/AgentBundle/Services/Api/Organizations';
import * as AgentTeams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Labels from 'DeskPRO/Bundle/AgentBundle/Services/Api/Labels';

export const loadUsersTotalCount = createAction(
  'CRM_NAV_LOAD_USERS_TOTAL_COUNT',
  trigger => People.loadUsersTotalCount().then(promise => trigger(promise.getData().data.count))
);

export const loadGroupsCounts = createAction(
  'CRM_NAV_LOAD_GROUPS_COUNTS',
  trigger => UserGroups.loadCounts().then(promise => trigger(promise.getData().data.nested))
);

export const loadOrganizationsTotalCount = createAction(
  'CRM_NAV_LOAD_ORG_TOTAL_COUNT',
  trigger => Organizations.loadCount().then(promise => trigger(promise.getData().data.count))
);

export const loadAgentsTotalCount = createAction(
  'CRM_NAV_LOAD_AGENTS_TOTAL_COUNT',
  trigger => People.loadAgentsTotalCount().then(promise => trigger(promise.getData().data.count))
);

export const loadTeamsCounts = createAction(
  'CRM_NAV_LOAD_TEAMS_COUNTS',
  trigger => AgentTeams.loadCounts().then(promise => trigger(promise.getData().data.nested))
);

export const loadPersonLabels = createAction(
  'CRM_NAV_LOAD_PERSON_LABELS',
  trigger => Labels.loadPersonLabels().then(promise => trigger(pluck(promise.getData().data, 'label')))
);

export const loadOrganizationLabels = createAction(
  'CRM_NAV_LOAD_ORGANIZATION_LABELS',
  trigger => Labels.loadOrganizationLabels().then(promise => trigger(pluck(promise.getData().data, 'label')))
);
