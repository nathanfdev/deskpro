import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as actions from '../Actions/reportActions';

const initialState = {
  reportsLoaded: false,
  currentReport: Immutable.fromJS({}),
  groupParams:   Immutable.fromJS({}),
};

export default createReducer(initialState, {
  [actions.loadReport]: async({
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload.widget)),
  }),
  [actions.newReport]: async({
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload)),
  }),
  [actions.reportsLoaded]:   state => state.set('reportsLoaded', true),
  [actions.loadGroupParams]: async({
    success: setFullPayload('groupParams'),
  })
});
