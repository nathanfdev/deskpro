import { createReducer } from 'DeskPRO/Component/Ampliflux';
import * as actions from '../Actions/sideBarActions';

const initialState = {
  current: 'menu_tickets'
};

export default createReducer(initialState, {
  [actions.changeSection]: (state, payload) => state.set('current', payload.section)
});
