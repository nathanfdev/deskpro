import { donePreloading } from '../Actions/bootstrapActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  isBootstrapped: false
};

export default createReducer(initialState, {
  [donePreloading]: state => state.set('isBootstrapped', true)
});
