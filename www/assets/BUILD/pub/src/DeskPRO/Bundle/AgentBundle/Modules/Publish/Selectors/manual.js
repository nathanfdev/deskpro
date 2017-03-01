import { createSelector } from 'reselect';

const stateSelector = state => state.Publish.manual;

export const treeSelector = createSelector(
  stateSelector,
  state => state.get('tree')
);
