import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue, mergeValue } from 'Ampliflux/reducers/handlers';
import {
  loadFilterSetsCount, loadFilterSets,
  startFilterEditing, applyFilterEditing, closeFilterEditing,
  loadLabels,
  loadStarsCount, loadStars,
  markFilterLoading, loadFilterCount
} from '../Actions/navActions';
import Immutable from 'immutable';

const initialState = {
  filterSetsCount: {},
  filterSets: [],
  filters: [],
  editedFilterId: null,
  labels: [],
  starsCount: [],
  stars: [],

  // async indicators
  done: {
    filterSets: false,
    filterSetsCount: false,
    labels: false,
    starsCount: false,
    stars: false
  },

  // async indicator for filters being loaded (array of filter IDs)
  filtersLoading: []
};

export default createReducer(initialState, {
  [startFilterEditing]: setFullPayload('editedFilterId'),
  [applyFilterEditing]: setFullPayload('editedFilterId'),
  [closeFilterEditing]: setFullPayload('editedFilterId'),

  'TICKETS_NAV_LOAD_FILTER_SETS': async({
    success: mergeFullPayload(),
    start: setValue('done.filterSets', false),
    done: setValue('done.filterSets', true)
  }),

  'TICKETS_NAV_LOAD_FILTER_SETS_COUNT': async({
    success: setFullPayload('filterSetsCount'),
    start: setValue('done.filterSetsCount', false),
    done: setValue('done.filterSetsCount', true)
  }),

  'TICKETS_NAV_LOAD_LABELS': async({
    success: setFullPayload('labels'),
    start: setValue('done.labels', false),
    done: setValue('done.labels', true)
  }),

  'TICKETS_NAV_LOAD_STARS_COUNT': async({
    success: setFullPayload('starsCount'),
    start: setValue('done.starsCount', false),
    done: setValue('done.starsCount', true)
  }),

  'TICKETS_NAV_LOAD_STARS': async({
    success: setFullPayload('stars'),
    start: setValue('done.stars', false),
    done: setValue('done.stars', true)
  }),

  'TICKETS_NAV_MARK_FILTER_AS_LOADING': (state, id) => {
    if (!state.get('filtersLoading').includes(id)) {
      return state.set('filtersLoading', state.get('filtersLoading').push(id));
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
            next = next.set(
              'filtersLoading',
              state.get('filtersLoading').delete(state.get('filtersLoading').indexOf(newFilterCount.group))
            );

            return next;
          }
        }
      }

      return state;
    }
  })
});
