import { createSelector } from 'reselect';
import { createTicketsRequestSelectors } from '../RecordStores/Selectors/ticketsSelectors';

export const ticketsSelector = createSelector(
  createTicketsRequestSelectors('tickets').recordsSel,
    tickets => tickets
);
