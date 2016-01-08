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

// Base widget options selectors
export const widgetBaseOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('widget')
);

export const widgetTypeSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('type') || 'default'
);

export const widgetPositionSelector = createSelector(
  widgetBaseOptionsSelector,
  options => `bottom.${options.get('position') || 'right'}`
);

export const isBubbleSelector = createSelector(
  widgetTypeSelector,
  widgetType => widgetType === 'bubble'
);

// Company options selectors
export const companyOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('company')
);

export const companyNameSelector = createSelector(
  companyOptionsSelector,
  options => options.get('name')
);

export const companyLogoSelector = createSelector(
  companyOptionsSelector,
  options => options.get('logo')
);

// Help button options selectors
export const helpButtonSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('button')
);

export const helpButtonSizeSelector = createSelector(
  helpButtonSelector,
  options => options.get('size')
);

export const helpButtonNameSelector = createSelector(
  helpButtonSelector,
  options => options.get('name') || 'Help'
);

export const helpButtonColorsSelector = createSelector(
  helpButtonSelector,
  options => options.get('colors')
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

// Chat options selectors
export const chatOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('chat')
);

export const widgetHasChatSelector = createSelector(
  chatOptionsSelector,
  options => options.get('enabled') !== undefined ? options.get('enabled') : true
);

export const chatModeSelector = createSelector(
  chatOptionsSelector,
  options => options.get('requestUserInfo') ? options.get('beginMode') : 'simple'
);

export const helpPopupSelector = createSelector(
  chatOptionsSelector,
  options => options.get('popup')
);

export const helpPopupTitleSelector = createSelector(
  helpPopupSelector,
  options => options.get('title')
);

export const helpPopupMessageSelector = createSelector(
  helpPopupSelector,
  options => options.get('message')
);

export const helpPopupReplyTypeSelector = createSelector(
  helpPopupSelector,
  options => options.get('replyType')
);

export const agentAcceptTimeoutSelector = createSelector(
  chatOptionsSelector,
  options => options.get('waitingTimeout') || 120 // 2 minutes
);

export const agentPollingTimeoutSelector = createSelector(
  chatOptionsSelector,
  options => options.get('agentPollingTimeout') || 'off'
);
