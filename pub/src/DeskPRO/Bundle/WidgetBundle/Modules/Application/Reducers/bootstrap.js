import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/bootstrapActions';
import { setValue, async } from 'Ampliflux/reducers/handlers';

const initialState = {
  loaded: false
};

export default createReducer(initialState, {
  [actions.bootstrapWidget]: async({
    done: setValue('loaded', true)
  })
});
