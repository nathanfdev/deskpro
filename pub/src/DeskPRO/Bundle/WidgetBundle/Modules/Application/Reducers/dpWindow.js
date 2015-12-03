import { createReducer } from 'Ampliflux';
import { setValue } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/dpWindowActions';

const initialState = {
  widgetOpened: false
};

export default createReducer(initialState, {
  [actions.openWidget]: setValue('widgetOpened', true),
  [actions.closeWidget]: setValue('widgetOpened', false)
});
