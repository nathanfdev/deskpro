import { createSelector } from 'reselect';
import { createOrganizationsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/organizationsSelectors';

export const organizationsRecordsSelector = createSelector(
  createOrganizationsRequestSelectors('crm').recordsSel,
    organizations => organizations
);
