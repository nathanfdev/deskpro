import { createReducer } from 'Ampliflux';
import { setValue, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/dpWindowActions';

const initialState = {
  options: {},
  triggerPopupOpened: false,
  widgetOpened: false,
  widgetDimensions: {width: null, height: null}
};

export default createReducer(initialState, {
  [actions.loadOptions]: setFullPayload('options'),
  [actions.windowResize]: setFullPayload('widgetDimensions'),

  [actions.openTriggerPopup]: setValue('triggerPopupOpened', true),
  [actions.closeTriggerPopup]: setValue('triggerPopupOpened', false),

  [actions.openWidget]: setValue('widgetOpened', true),
  [actions.closeWidget]: setValue('widgetOpened', false)
});
