import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setValue, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/dpWindowActions';

const initialState = {
  options:            {},
  triggerPopupOpened: false,
  widgetOpened:       false,

  dimensions: {
    window: {
      width:  null,
      height: null
    },
    widget: {
      width:  null,
      height: null
    }
  }
};

export default createReducer(initialState, {
  [actions.loadOptions]: setFullPayload('options'),

  [actions.windowResize]: setFullPayload('dimensions.window'),
  [actions.widgetResize]: setFullPayload('dimensions.widget'),

  [actions.openTriggerPopup]:  setValue('triggerPopupOpened', true),
  [actions.closeTriggerPopup]: setValue('triggerPopupOpened', false),

  [actions.openWidget]:   setValue('widgetOpened', true),
  [actions.reopenWidget]: setValue('widgetOpened', true),
  [actions.closeWidget]:  setValue('widgetOpened', false)
});
