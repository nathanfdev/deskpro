import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import { loadQrCode } from '../Actions/preferencesActions';

const initialState = {
  setup_token: ''
};
export default createReducer(initialState, {
  [loadQrCode]: async({success: setFullPayload('setup_token')})
});
