import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { clickLogo } from '../../TopBar/Actions/topbarActions';

const initialState = {};

export default createReducer(initialState, {
  [clickLogo]: (state, payload) => {
    console.log('click logo');
    console.log(state);
    console.log(payload);
    return state;
  }
});
