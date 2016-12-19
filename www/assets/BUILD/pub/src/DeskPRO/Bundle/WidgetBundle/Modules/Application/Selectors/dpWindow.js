import Immutable from 'immutable';
import { createSelector } from 'reselect';
import { sessionLanguageSelector } from './bootstrap';

export const translationsSelectorFactory = (property, defaultValue) => (options, language) => {
  const translations = options.get('translations') || [];
  const filtered = translations.filter(translation => translation.get('language') === language);

  if (filtered.size) {
    const currentLanguageValue = filtered.first().get(property);
    if (currentLanguageValue) {
      return currentLanguageValue;
    }
  }
  if (translations.size) {
    const defaultLanguageValue = translations.first().get(property);
    if (defaultLanguageValue) {
      return defaultLanguageValue;
    }
  }

  return defaultValue;
};

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

export const isFullScreenSelector = createSelector(
  windowDimensionsSelector,
  windowDimensions => windowDimensions.get('width') < 450
);

export const widgetDimensionsSelector = createSelector(
  dimensionsSelector,
  dimensions => dimensions.get('widget')
);

export const widgetHeightSelector = createSelector(
  widgetDimensionsSelector,
  widgetDimensions => widgetDimensions.get('height')
);

export const widgetBodyHeightSelector = createSelector(
  widgetHeightSelector,
  widgetHeight => (widgetHeight > 92 ? widgetHeight - 92 : 0)
);

// Options selectors
export const widgetOptionsSelector = createSelector(
  stateSelector,
  state => state.get('options') || Immutable.fromJS({})
);

export const noFetchOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('noFetchOptions')
);

// Base widget options selectors
export const widgetBaseOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('widget') || Immutable.fromJS({})
);

export const widgetTypeSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('type') || 'default'
);

export const widgetRawPositionSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('position')
);

export const widgetEnabledSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('enabled')
);

export const widgetPositionSelector = createSelector(
  widgetRawPositionSelector,
  position => `bottom.${position || 'right'}`
);

export const isBubbleSelector = createSelector(
  widgetTypeSelector,
  isFullScreenSelector,
  (widgetType, fullScreen) => widgetType === 'bubble' && !fullScreen
);

export const liveDemoSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('live_demo')
);

export const agentPollingTimeoutSelector = createSelector(
  widgetBaseOptionsSelector,
  options => options.get('agent_polling_timeout') || 'off'
);

// Help button options selectors
export const helpButtonSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('button') || Immutable.fromJS({})
);

export const helpButtonSizeSelector = createSelector(
  helpButtonSelector,
  options => options.get('size')
);

export const widgetLanguageSelector = createSelector(
  widgetOptionsSelector,
  sessionLanguageSelector,
  (options, sessionLanguage) => {
    if (options.get('language')) {
      return options.get('language');
    }
    return sessionLanguage;
  }
);

export const helpButtonNameSelector = createSelector(
  helpButtonSelector,
  widgetLanguageSelector,
  translationsSelectorFactory('name', 'Help')
);

export const helpButtonColorsSelector = createSelector(
  helpButtonSelector,
  options => options.get('colors') || Immutable.fromJS({})
);

export const helpButtonBackgroundColorSelector = createSelector(
  helpButtonColorsSelector,
  options => options.get('background')
);

export const helpButtonTextColorSelector = createSelector(
  helpButtonColorsSelector,
  options => options.get('text')
);

// Chat options selectors
export const chatOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('chat') || Immutable.fromJS({})
);

export const widgetProactiveChatSelector = createSelector(
  chatOptionsSelector,
  options => (options.get('proactive') !== undefined ? options.get('proactive') : true)
);

export const widgetAllowDepartmentSelection = createSelector(
  chatOptionsSelector,
  options => (options.get('allow_department_selection') !== undefined ? options.get('allow_department_selection') : false)
);

export const chatBeginModeSelector = createSelector(
  chatOptionsSelector,
  options => (options.get('request_user_info') ? options.get('begin_mode') : 'simple')
);

export const helpPopupSelector = createSelector(
  chatOptionsSelector,
  options => options.get('popup') || Immutable.fromJS({})
);

export const helpPopupTitleSelector = createSelector(
  helpPopupSelector,
  widgetLanguageSelector,
  translationsSelectorFactory('title', 'Customer Support')
);

export const helpPopupMessageSelector = createSelector(
  helpPopupSelector,
  widgetLanguageSelector,
  translationsSelectorFactory('message', 'Need help? Just reply to start a live chat with one of our team.')
);

export const helpPopupHeadingSelector = createSelector(
  helpPopupSelector,
  widgetLanguageSelector,
  translationsSelectorFactory('heading', 'Ask us a question!')
);

export const helpPopupSubheadingSelector = createSelector(
  helpPopupSelector,
  widgetLanguageSelector,
  translationsSelectorFactory(
    'subheading',
    'Our team are online and ready to help with your enquiries. Send us a message to get started.'
  )
);

export const helpPopupStartButtonSelector = createSelector(
  helpPopupSelector,
  widgetLanguageSelector,
  translationsSelectorFactory(
    'start_button',
    'Start a conversation'
  )
);

export const widgetPopupStyleSelector = createSelector(
  helpPopupSelector,
  options => options.get('style')
);

export const agentAcceptTimeoutSelector = createSelector(
  chatOptionsSelector,
  options => options.get('waiting_timeout') || 120 // 2 minutes
);

// Ticket options selectors
export const ticketOptionsSelector = createSelector(
  widgetOptionsSelector,
  options => options.get('ticket')
);

export const ticketDefaultDepartmentSelector = createSelector(
  ticketOptionsSelector,
  options => options.get('default_department')
);

export const ticketSelectDepartmentTypeSelector = createSelector(
  ticketOptionsSelector,
  options => options.get('select_department')
);

export const ticketDefaultSubjectSelector = createSelector(
  ticketOptionsSelector,
  options => options.get('default_subject')
);

export const ticketSelectSubjectTypeSelector = createSelector(
  ticketOptionsSelector,
  options => options.get('select_subject')
);

export const isTicketDepartmentFieldHidden = createSelector(
  ticketDefaultDepartmentSelector,
  ticketSelectDepartmentTypeSelector,
  (defaultDepartment, selectDepartmentType) => selectDepartmentType === 'default' && defaultDepartment > 0
);

export const isTicketSubjectFieldHidden = createSelector(
  ticketDefaultSubjectSelector,
  ticketSelectSubjectTypeSelector,
  (defaultSubject, selectSubjectType) => (selectSubjectType === 'default' && defaultSubject)
    || selectSubjectType === 'message'
);
