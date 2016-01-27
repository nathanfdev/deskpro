import { donePreloading } from '../Actions/bootstrapActions';
import { createReducer } from 'Ampliflux';
import { async, setValue } from 'Ampliflux/reducers/handlers';

const initialState = {
  isBootstrapped: false
};

export default createReducer(initialState, {
  [donePreloading]: state => state.set('isBootstrapped', true)
});
