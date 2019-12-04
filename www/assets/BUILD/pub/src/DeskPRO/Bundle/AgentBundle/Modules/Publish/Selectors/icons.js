import { createSelector } from 'reselect';

const stateSelector = state => state.Publish.iconPicker;

export const iconsSelector = createSelector(
  stateSelector,
  state => state.get('icons')
);
