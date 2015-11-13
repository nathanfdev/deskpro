import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import { toggleAll, toggleSelected, toggleTableFieldVisibility, toggleCardFieldVisibility, setViewMode, unload }
  from '../Actions/listActions';

const initialState = {
  viewMode: 'card',
  elements: [],
  listParams: {
    sort: 'date_created',
    order: 'asc',
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

  [toggleAll]: (state, select) => {
    let selected = state.get('selected');
    if (select) {
      state.get('elements').map(el => selected.includes(el.get('id')) || (selected = selected.push(el.get('id'))));
    } else {
      selected = selected.clear();
    }

    return state.set('selected', selected);
  },
  [toggleTableFieldVisibility]: togglePayloadInCollection('tableVisibleFields'),
  [toggleCardFieldVisibility]: togglePayloadInCollection('cardVisibleFields'),
  [setViewMode]: setFullPayload('viewMode')
});
