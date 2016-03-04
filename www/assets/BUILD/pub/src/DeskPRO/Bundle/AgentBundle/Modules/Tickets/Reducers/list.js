import { createReducer } from 'Ampliflux';
import { setFullPayload, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import { toggleTableFieldVisibility, toggleCardFieldVisibility, setViewMode } from '../Actions/listActions';

export const ticketsListInitialState = {
  viewMode: 'card',
  listParams: {
    order_by: 'urgency',
    order_dir: 'desc',
    filter: null
  },
  pagination: {},

  tableVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status'],
  cardVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status', 'date_created', 'labels']
};

export default createReducer(ticketsListInitialState, {

  // Private -----------------------------------------------------------------------------------------------------------

  TICKETS_LIST_SET_LIST_PARAMS: setFullPayload('listParams'),
  TICKETS_LIST_SET_PAGINATION: setFullPayload('pagination'),

  // Public (control bar) ----------------------------------------------------------------------------------------------

  [toggleTableFieldVisibility]: togglePayloadInCollection('tableVisibleFields'),
  [toggleCardFieldVisibility]: togglePayloadInCollection('cardVisibleFields'),
  [setViewMode]: setFullPayload('viewMode')
});
