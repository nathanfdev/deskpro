import { createSelector } from 'reselect';
import { createOrganizationsRequestSelectors }
  from '../RecordStores/Selectors/organizationsSelectors';
import { createUserGroupsRequestSelectors }
  from '../RecordStores/Selectors/userGroupsSelectors.js';
import { createLanguagesRequestSelectors }
  from '../../Common/RecordStores/Selectors/languagesSelectors.js';

export const organizationsRecordsSelector = createSelector(
  createOrganizationsRequestSelectors('crm').recordsSel,
    organizations => organizations
);

export const userGroupsSelector = createSelector(
  createUserGroupsRequestSelectors('crm').recordsSel,
    organizations => organizations
);

export const languagesSelector = createSelector(
  createLanguagesRequestSelectors('crm').recordsSel,
    organizations => organizations
);

