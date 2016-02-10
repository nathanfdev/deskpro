import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from '../RecordStores/Selectors/peopleSelectors';
import { createUserGroupsRequestSelectors }
  from '../RecordStores/Selectors/userGroupsSelectors.js';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('crm').recordsSel,
    people => people
);

export const userGroupsSelector = createSelector(
  createUserGroupsRequestSelectors('crm').recordsSel,
    organizations => organizations
);
