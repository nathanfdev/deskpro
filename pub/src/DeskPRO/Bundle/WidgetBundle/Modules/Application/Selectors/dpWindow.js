import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;

export const widgetOpenedSelector = createSelector(
  stateSelector,
  state => state.get('widgetOpened')
);

// Trigger selectors
export const triggerPopupOpenedSelector = createSelector(
  stateSelector,
  state => state.get('triggerPopupOpened')
);

// Dimensions selectors
export const dimensionsSelector = createSelector(
  stateSelector,
  state => state.get('dimensions')
);

export const windowDimensionsSelector = createSelector(
  dimensionsSelector,
  dimensions => dimensions.get('window')
);

export const widgetDimensionsSelector = createSelector(
  dimensionsSelector,
  dimensions => dimensions.get('widget')
);

export const widgetHeightSelector = createSelector(
  widgetDimensionsSelector,
  widgetDimensions => widgetDimensions.get('height')
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

export const helpButtonNameSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpButtonName') || 'Help'
);

export const helpButtonColorsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('helpButtonColors')
);

export const helpButtonBackgroundColorSelector = createSelector(
  helpButtonColorsSelector,
  options => options.get('background')
);

export const helpButtonTextColorSelector = createSelector(
  helpButtonColorsSelector,
  options => options.get('text')
);

export const helpButtonBorderColorSelector = createSelector(
  helpButtonColorsSelector,
  options => options.get('border')
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

export const agentAcceptTimeoutSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('agentAcceptTimeout') || 120 // 2 minutes
);

export const widgetPositionSelector = createSelector(
  widgetOptionsSelector,
  options => `bottom.${options.get('windowPosition') || 'right'}`
);

export const widgetTypeSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('windowType') || 'default'
);

export const isBubbleSelector = createSelector(
  widgetTypeSelector,
  widgetType => widgetType === 'bubble'
);

export const widgetHasChatSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('hasChat') !== undefined ? options.get('hasChat') : true
);
