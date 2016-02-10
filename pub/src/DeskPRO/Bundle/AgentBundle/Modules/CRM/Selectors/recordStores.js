import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from '../RecordStores/Selectors/peopleSelectors';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('crm').recordsSel,
    people => people
);

