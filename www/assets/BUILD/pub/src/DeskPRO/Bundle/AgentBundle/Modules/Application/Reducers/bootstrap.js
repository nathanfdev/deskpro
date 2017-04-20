import { createReducer } from 'Ampliflux';
import { donePreloading } from '../Actions/bootstrapActions';

const initialState = {
  isBootstrapped: false
};

export default createReducer(initialState, {
  [donePreloading]: state => state.set('isBootstrapped', true)
});
