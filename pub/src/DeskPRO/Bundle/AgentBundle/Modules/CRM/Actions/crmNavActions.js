import { createAction } from 'Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { pluck } from 'lodash';

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

      api.sendGet(batch).success(({responses}) => {
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
