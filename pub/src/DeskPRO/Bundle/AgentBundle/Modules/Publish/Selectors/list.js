import { createSelector } from 'reselect';

const stateSelector = state => state.Publish.list;

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);
