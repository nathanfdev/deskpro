import { createAction } from 'Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { pluck } from 'lodash';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as UserGroups from 'DeskPRO/Bundle/AgentBundle/Services/Api/UserGroups';
import * as Organizations from 'DeskPRO/Bundle/AgentBundle/Services/Api/Organizations';
import * as AgentTeams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Labels from 'DeskPRO/Bundle/AgentBundle/Services/Api/Labels';

export const initialLoad = createAction(
  'CRM_NAV_INITIAL_LOAD',
  () => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
          + '?get[users]=DP_API/people/counts?is_agent%3D0%26is_deleted%3D0%26group_by%3Duser_group'
          + '&get[agents]=DP_API/people/counts?is_agent%3D1%26is_deleted%3D0%26group_by%3Dagent_team'
          + '&get[organizations]=DP_API/organizations/counts'
          + '&get[personLabels]=DP_API/person_labels'
          + '&get[organizationLabels]=DP_API/organization_labels'
        ;

      DpApi.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.labels = {
          person: pluck(payload.personLabels, 'label'),
          organization: pluck(payload.organizationLabels, 'label')
        };
        delete payload.personLabels;
        delete payload.organizationLabels;
        resolve(payload);
      });
    }
  )
);

export const loadUsersTotalCount = createAction(
  'CRM_NAV_LOAD_USERS_TOTAL_COUNT',
  () => People.loadUsersTotalCount().then(promise => promise.getData().data.count)
);

export const loadGroupsCounts = createAction(
  'CRM_NAV_LOAD_GROUPS_COUNTS',
  () => UserGroups.loadCounts().then(promise => promise.getData().data.nested)
);

export const loadOrganizationsTotalCount = createAction(
  'CRM_NAV_LOAD_ORG_TOTAL_COUNT',
  () => Organizations.loadCount().then(promise => promise.getData().data.count)
);

export const loadAgentsTotalCount = createAction(
  'CRM_NAV_LOAD_AGENTS_TOTAL_COUNT',
  () => People.loadAgentsTotalCount().then(promise => promise.getData().data.count)
);

export const loadTeamsCounts = createAction(
  'CRM_NAV_LOAD_TEAMS_COUNTS',
  () => AgentTeams.loadCounts().then(promise => promise.getData().data.nested)
);

export const loadPersonLabels = createAction(
  'CRM_NAV_LOAD_PERSON_LABELS',
  () => Labels.loadPersonLabels().then(promise => pluck(promise.getData().data, 'label'))
);

export const loadOrganizationLabels = createAction(
  'CRM_NAV_LOAD_ORGANIZATION_LABELS',
  () => Labels.loadOrganizationLabels().then(promise => pluck(promise.getData().data, 'label'))
);
