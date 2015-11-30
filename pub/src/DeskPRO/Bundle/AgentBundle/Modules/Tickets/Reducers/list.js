import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async, togglePayloadInCollection, handleMassAction } from 'Ampliflux/reducers/handlers';
import { toggleAll, toggleSelected, toggleTableFieldVisibility, toggleCardFieldVisibility, setViewMode, unload }
  from '../Actions/listActions';

const initialState = {
  viewMode: 'card',
  elements: [],
  listParams: {
    sort: 'urgency',
    order: 'desc',
    filter: null
  },
  selected: [],

  // async indicators
  async: {
    done: null
  },

  tableVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status'],
  cardVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status', 'date_created', 'labels']
};

export default createReducer(initialState, {

  // Private -----------------------------------------------------------------------------------------------------------

  TICKETS_LIST_SET_LIST_PARAMS: setFullPayload('listParams'),
  TICKETS_LIST_LOAD_LIST: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),

  // Public ------------------------------------------------------------------------------------------------------------

  [toggleSelected]: togglePayloadInCollection('selected'),
  [unload]: setValue('elements', []),

  // Public (control bar) ----------------------------------------------------------------------------------------------

  [toggleAll]: handleMassAction('elements', 'selected'),
  [toggleTableFieldVisibility]: togglePayloadInCollection('tableVisibleFields'),
  [toggleCardFieldVisibility]: togglePayloadInCollection('cardVisibleFields'),
  [setViewMode]: setFullPayload('viewMode')
});
