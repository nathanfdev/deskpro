import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import { startFilterEditing, applyFilterEditing, closeFilterEditing, initialLoad, unload } from '../Actions/navActions';

const initialState = {
  filterSetsCount: {},
  filterSets: [],
  filters: [],
  labels: [],
  starsCount: [],
  stars: [],

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

  TICKET_NAV_UPDATE_FILTER: (state, targetFilter) => {
    let index = null;
    state.get('filters').forEach((filter, i) => {
      if (filter.get('id') === targetFilter.id) {
        index = i;
        return false;
      }
    });

    return index !== null ? state.mergeIn(['filters', index], targetFilter) : state;
  },

  TICKETS_NAV_LOAD_FILTER_COUNT: async({
    success: (state, newFilterCount) => {
      const filterSets = state.get('filterSetsCount').toJS();

      for (let i = 0; i < filterSets.length; i++) {
        for (let j = 0; j < filterSets[i].nested.length; j++) {
          if (filterSets[i].nested[j].group === newFilterCount.group) {
            filterSets[i].nested[j] = newFilterCount;

            let next = setFullPayload('filterSetsCount')(state, filterSets);
            next = next.setIn(
              ['async', 'filtersLoading'],
              state.getIn(['async', 'filtersLoading'])
                   .delete(state.getIn(['async', 'filtersLoading']).indexOf(newFilterCount.group))
            );

            return next;
          }
        }
      }

      return state;
    }
  }),

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
