import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;

export const widgetOpenedSelector = createSelector(
  stateSelector,
  state => state.get('widgetOpened')
);

// Widget dimensions selectors
export const widgetDimensionsSelector = createSelector(
  stateSelector,
  state => state.get('widgetDimensions')
);

export const widgetHeightSelector = createSelector(
  widgetDimensionsSelector,
  dimensions => dimensions.get('height')
);

// Options selectors
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

export const helpButtonSizeSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpButtonSize')
);

export const chatModeSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('chatMode')
);

export const helpPopupSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpPopup')
);

export const helpPopupTitleSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpPopupTitle')
);

export const helpPopupMessageSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpPopupMessage')
);

export const agentPollingTimeoutSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('agentPollingTimeout') || 'off'
);

export const windowTypeSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('windowType') || 'default'
);