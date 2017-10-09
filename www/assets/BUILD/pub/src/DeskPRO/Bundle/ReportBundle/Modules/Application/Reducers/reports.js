import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as actions from '../Actions/reportActions';

const initialState = {
  customReports:        Immutable.fromJS({}),
  builtInReports:       Immutable.fromJS({}),
  customReportsLoaded:  false,
  builtInReportsLoaded: false,
  currentReport:        Immutable.fromJS({}),
};

export default createReducer(initialState, {
  [actions.loadCustomReports]: async({
    start:   state => state.set('customReportsLoaded', false),
    success: setFullPayload('customReports'),
    done:    state => state.set('customReportsLoaded', true)
  }),
  [actions.loadBuiltInReports]: async({
    start:   state => state.set('builtInReportsLoaded', false),
    success: setFullPayload('builtInReports'),
    done:    state => state.set('builtInReportsLoaded', true)
  }),
  [actions.loadReport]: async({
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload.widget)),
  })
});
