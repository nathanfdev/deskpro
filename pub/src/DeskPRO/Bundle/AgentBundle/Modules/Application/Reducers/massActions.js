import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import * as massActions from '../Actions/massActions';
import { async, setValue, setFullPayload, togglePayloadInCollection, handleMassAction, mergeFullPayload }
  from 'Ampliflux/reducers/handlers';

const initialState = {
  selected: Immutable.fromJS([]) // array of IDs
};

export default createReducer(initialState, {
  [massActions.toggleMassAction]: handleMassAction(),
  [massActions.toggleSelectedAction]: togglePayloadInCollection('selected'),

})