import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload } from 'Ampliflux/reducers/handlers';
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
  stars: []
};

export default createReducer(initialState, {
  [loadFilterSets]: async({success: mergeFullPayload()}),
  [loadFilterSetsCount]: async({success: setFullPayload('filterSetsCount')}),
  [startFilterEditing]: setFullPayload('editedFilterId'),
  [applyFilterEditing]: setFullPayload('editedFilterId'),
  [closeFilterEditing]: setFullPayload('editedFilterId'),
  [loadLabels]: async({success: setFullPayload('labels')}),
  [loadStarsCount]: async({success: setFullPayload('starsCount')}),
  [loadStars]: async({success: setFullPayload('stars')})
});
