import { createSelector } from 'reselect';

const stateSelector = state => state.Publish.guide;

export const treeSelector = createSelector(
  stateSelector,
  state => state.get('tree')
);
