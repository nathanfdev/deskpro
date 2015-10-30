import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import {
  loadFilterSetsCount, loadFilterSets,
  startFilterEditing, applyFilterEditing, closeFilterEditing,
  loadLabels,
  loadStarsCount, loadStars
} from '../Actions/navActions';

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
  }
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
  })
});
