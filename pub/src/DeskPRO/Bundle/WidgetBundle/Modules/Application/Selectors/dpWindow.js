import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;

export const widgetOpenedSelector = createSelector(
  stateSelector,
  state => state.get('widgetOpened')
);

export const widgetDimensionsSelector = createSelector(
  stateSelector,
  state => state.get('widgetDimensions')
);

export const widgetHeightSelector = createSelector(
  widgetDimensionsSelector,
  dimensions => dimensions.get('height')
);

export const widgetOptionsSelector = createSelector(
  stateSelector,
  state => state.get('options')
);

export const companyNameSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('companyName')
);

export const companyLogoSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('companyLogo')
);
