import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async } from 'Ampliflux/reducers/handlers';
import { toggleMassAction, toggleSelected } from '../Actions/listActions';

const initialState = {
  mode: 'list',
  elements: [],
  listParams: {
    filter: 1
  },
  selected: [],

  // async indicators
  async: {
    done: null
  }
};

export default createReducer(initialState, {
  TICKETS_LIST_SET_LIST_PARAMS: setFullPayload('listParams'),
  TICKETS_LIST_LOAD_LIST: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [toggleMassAction]: (state, select) => {
    let selected = state.get('selected');
    if (select) {
      state.get('elements').map(e => selected.includes(e.get('id')) || (selected = selected.push(e.get('id'))));
    } else {
      selected = selected.clear();
    }

    return state.set('selected', selected);
  },
  [toggleSelected]: (state, id) => {
    let selected = state.get('selected');
    selected = selected.includes(id) ? selected.delete(selected.indexOf(id)) : selected.push(id);

    return state.set('selected', selected);
  }
});
