import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/clientActions';

const initialState = {
  tokens:      {},
  activities:  {},
  reservation: null
};

export default createReducer(initialState, {
  [actions.setVoiceTokens]:     setFullPayload('tokens'),
  [actions.setVoiceActivities]: setFullPayload('activities'),
  [actions.setReservation]:     setFullPayload('reservation')
});
