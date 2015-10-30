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
  [loadFilterSets]: async({
    success: mergeFullPayload(),
    start: setValue('done.filterSets', false),
    done: setValue('done.filterSets', true)
  }),

  [loadFilterSetsCount]: async({
    success: setFullPayload('filterSetsCount'),
    start: setValue('done.filterSetsCount', false),
    done: setValue('done.filterSetsCount', true)
  }),

  [startFilterEditing]: setFullPayload('editedFilterId'),
  [applyFilterEditing]: setFullPayload('editedFilterId'),
  [closeFilterEditing]: setFullPayload('editedFilterId'),

  [loadLabels]: async({
    success: setFullPayload('labels'),
    start: setValue('done.labels', false),
    done: setValue('done.labels', true)
  }),

  [loadStarsCount]: async({
    success: setFullPayload('starsCount'),
    start: setValue('done.starsCount', false),
    done: setValue('done.starsCount', true)
  }),

  [loadStars]: async({
    success: setFullPayload('stars'),
    start: setValue('done.stars', false),
    done: setValue('done.stars', true)
  }),

  [markFilterLoading]: (state, id) => {
    if (!state.get('filtersLoading').includes(id)) {
      return state.set('filtersLoading', state.get('filtersLoading').push(id));
    }

    return state;
  },

  [loadFilterCount]: async({
    success: (state, newFilterCount) => {
      const filterSetsCount = state.get('filterSetsCount');

      for (let filterSetCountIndex = 0; filterSetCountIndex < filterSetsCount.length; filterSetCountIndex++) {
        const filterSetCount = filterSetsCount[filterSetCountIndex];
        for (let filterCountIndex = 0; filterCountIndex < filterSetCount.nested.length; filterCountIndex++) {
          const filterCount = filterSetCount.nested[filterCountIndex];
          if (filterCount.group === newFilterCount.group) {
            filterSetsCount[filterSetCountIndex].nested[filterCountIndex] = newFilterCount;

            let next = state.set('filterSetsCount', filterSetsCount);
            next = next.set(
              'filtersLoading',
              state.get('filtersLoading').delete(state.get('filtersLoading').indexOf(filterCount.group))
            );

            return next;
          }
        }
      }

      return state;
    }
  })
});
