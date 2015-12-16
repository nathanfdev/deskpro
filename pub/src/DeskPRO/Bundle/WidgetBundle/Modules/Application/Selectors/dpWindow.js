import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;

export const widgetOpenedSelector = createSelector(
  stateSelector,
  state => state.get('widgetOpened')
);

export const widgetDimentionsSelector = createSelector(
  stateSelector,
  state => state.get('widgetDimensions')
);

export const widgetHeightSelector = createSelector(
  widgetDimentionsSelector,
  state => state.get('height')
);
