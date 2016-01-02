import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from '../RecordStores/Selectors/peopleSelectors';
import { createOrganizationsRequestSelectors }
  from '../RecordStores/Selectors/organizationsSelectors';
import { createUserGroupsRequestSelectors }
  from '../RecordStores/Selectors/userGroupsSelectors.js';
import { createLanguagesRequestSelectors }
  from '../../Common/RecordStores/Selectors/languagesSelectors.js';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('crm').recordsSel,
    people => people
);

export const organizationsSelector = createSelector(
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

