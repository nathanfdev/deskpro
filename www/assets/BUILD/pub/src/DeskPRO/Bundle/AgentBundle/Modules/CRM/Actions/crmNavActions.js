import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const initialLoad = createAction(
  'CRM_NAV_INITIAL_LOAD',
  () => new Promise(resolve => {
    const batchComponents = {
      users:              { endpoint: 'people/counts', query: 'is_agent=0&is_deleted=0&group_by=user_group' },
      agents:             { endpoint: 'people/counts', query: 'is_agent=1&is_deleted=0&group_by=agent_team' },
      organizations:      { endpoint: 'organizations/counts' },
      personLabels:       { endpoint: 'person_labels' },
      organizationLabels: { endpoint: 'organization_labels' }
    };

    const batch = api.prepareParams(batchComponents);

    api.sendGet(batch).success(({ responses }) => resolve(flattenBatchResponses(responses)));
  })
);
