import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import * as massActions from '../Actions/massActions';
import { togglePayloadInCollection, handleMassAction, mergeFullPayload } from 'Ampliflux/reducers/handlers';

const massActionsInitialState = {
  selected: Immutable.fromJS([]) // array of IDs
};

export default createReducer(massActionsInitialState, {
  [massActions.toggleMassAction]: handleMassAction(),
  [massActions.toggleSelectedAction]: togglePayloadInCollection('selected'),
  [massActions.cancelMassActions]: state => state.set('params', Immutable.fromJS({})),
  [massActions.setMassActionsParams]: mergeFullPayload('params'),
  [massActions.resetParam]: (state, payload) => state.deleteIn(['params', payload])
})