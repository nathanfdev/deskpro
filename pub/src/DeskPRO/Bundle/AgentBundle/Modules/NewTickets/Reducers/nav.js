import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import {
  initialLoad,
  startFilterEditing, applyFilterEditing, closeFilterEditing
} from '../Actions/navActions';
import Immutable from 'immutable';

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
  [startFilterEditing]: setFullPayload('editedFilterId'),
  [applyFilterEditing]: setFullPayload('editedFilterId'),
  [closeFilterEditing]: setFullPayload('editedFilterId'),
  [initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),

  'TICKETS_NAV_MARK_FILTER_AS_LOADING': (state, id) => {
    if (!state.getIn(['async', 'filtersLoading']).includes(id)) {
      return state.setIn(['async', 'filtersLoading'], state.getIn(['async', 'filtersLoading']).push(id));
    }

    return state;
  },

  'TICKETS_NAV_LOAD_FILTER_COUNT': async({
    success: (state, newFilterCount) => {
      const filterSets = state.get('filterSetsCount').toJS();

      for (let i = 0; i < filterSets.length; i++) {
        for (let j = 0; j < filterSets[i].nested.length; j++) {
          if (filterSets[i].nested[j].group === newFilterCount.group) {
            filterSets[i].nested[j] = newFilterCount;

            let next = state.set('filterSetsCount', Immutable.fromJS(filterSets));
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
  })
});
