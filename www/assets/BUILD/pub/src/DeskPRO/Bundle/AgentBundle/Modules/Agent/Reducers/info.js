import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'Ampliflux/reducers/handlers';
import { setAgentInfo } from '../Actions/infoActions';

const initialState = {};
export default createReducer(initialState, {
  [setAgentInfo]: setFullPayload(),
});
