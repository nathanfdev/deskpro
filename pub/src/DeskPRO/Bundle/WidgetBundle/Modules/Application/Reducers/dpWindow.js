import { createReducer } from 'Ampliflux';
import { setValue, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/dpWindowActions';

const initialState = {
  widgetOpened: false,
  widgetDimensions: {width: null, height: null}
};

export default createReducer(initialState, {
  [actions.openWidget]: setValue('widgetOpened', true),
  [actions.closeWidget]: setValue('widgetOpened', false),
  [actions.windowResize]: setFullPayload('widgetDimensions')
});
