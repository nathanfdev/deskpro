import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import { startFilterEditing, applyFilterEditing, closeFilterEditing, initialLoad, unload } from '../Actions/navActions';

const initialState = {
  filterSetsCount: {},
  filters: [],
  labels: [],
  starsCount: [],

  editedFilterId: null,

  // async indicators
  async: {
    done: false,       // initial load
    filtersLoading: [] // filters being loaded (array of filter IDs)
  }
};

export default createReducer(initialState, {

  // Private -----------------------------------------------------------------------------------------------------------

  TICKETS_NAV_MARK_FILTER_AS_LOADING: (state, id) => {
    if (!state.getIn(['async', 'filtersLoading']).includes(id)) {
      return state.setIn(['async', 'filtersLoading'], state.getIn(['async', 'filtersLoading']).push(id));
    }

    return state;
  },

  TICKETS_NAV_LOAD_FILTER_COUNT: async({
    success: (state, newFilterCount) => {
      const filterSets = state.get('filterSetsCount').toJS();

      for (let i = 0; i < filterSets.length; i++) {
        for (let j = 0; j < filterSets[i].nested.length; j++) {
          if (filterSets[i].nested[j].id === newFilterCount.id) {
            filterSets[i].nested[j] = newFilterCount;

            let next = setFullPayload('filterSetsCount')(state, filterSets);
            next = next.setIn(
              ['async', 'filtersLoading'],
              state.getIn(['async', 'filtersLoading'])
                   .delete(state.getIn(['async', 'filtersLoading']).indexOf(newFilterCount.id))
            );

            return next;
          }
        }
      }

      return state;
    }
  }),

  TICKET_NAV_REMOVE_FILTER_NESTED_COUNTS: (state, id) => {
    const filterSets = state.get('filterSetsCount').toJS();
    for (let i = 0; i < filterSets.length; i++) {
      for (let j = 0; j < filterSets[i].nested.length; j++) {
        if (filterSets[i].nested[j].id === id) {
          filterSets[i].nested[j].nested = [];
          return setFullPayload('filterSetsCount')(state, filterSets);
        }
      }
    }

    return state;
  },

  // Public ------------------------------------------------------------------------------------------------------------

  [startFilterEditing]: setFullPayload('editedFilterId'),
  [closeFilterEditing]: setFullPayload('editedFilterId'),
  [applyFilterEditing]: setFullPayload('editedFilterId'),
  [initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
