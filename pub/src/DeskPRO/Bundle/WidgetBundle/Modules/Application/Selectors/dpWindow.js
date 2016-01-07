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
export const widgetTypeSelector = createSelector(
  widgetOptionsSelector,
  options => options.getIn(['widget', 'type']) || 'default'
);

export const widgetPositionSelector = createSelector(
  widgetOptionsSelector,
  options => `bottom.${options.getIn(['widget', 'position']) || 'right'}`
);

export const isBubbleSelector = createSelector(
  widgetTypeSelector,
  widgetType => widgetType === 'bubble'
);

export const agentPollingTimeoutSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('agentPollingTimeout') || 'off'
);

// Company options selectors
export const companySelector = createSelector(
  widgetOptionsSelector,
  options => options.get('company')
);

export const companyNameSelector = createSelector(
  companySelector,
  company => company.get('name')
);

export const companyLogoSelector = createSelector(
  companySelector,
  company => company.get('logo')
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

export const helpPopupTitleSelector = createSelector(
  chatOptionsSelector,
  options => options.getIn(['popup', 'title'])
);

export const helpPopupMessageSelector = createSelector(
  chatOptionsSelector,
  options => options.getIn(['popup', 'message'])
);

export const helpPopupReplyTypeSelector = createSelector(
  chatOptionsSelector,
  options => options.getIn(['popup', 'replyType'])
);

export const agentAcceptTimeoutSelector = createSelector(
  chatOptionsSelector,
  options => options.get('waitingTimeout') || 120 // 2 minutes
);
