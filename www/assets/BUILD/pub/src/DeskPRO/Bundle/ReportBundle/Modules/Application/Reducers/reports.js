import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as actions from '../Actions/reportActions';

const initialState = {
  customReports:  Immutable.fromJS([]),
  builtInReports: Immutable.fromJS([]),
  reportsLoaded:  false,
  currentReport:  Immutable.fromJS({}),
  groupParams:    Immutable.fromJS({}),
  labels:         Immutable.fromJS([])
};

export default createReducer(initialState, {
  [actions.loadReport]: async({
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload.widget)),
  }),
  [actions.loadReports]: async({
    start:   state => state.set('reportsLoaded', false),
    success: (state, payload) => {
      const customReports  = payload.reports.filter(report => report.is_custom === true);
      const builtInReports = payload.reports.filter(report => report.is_custom === false);
      const labels         = Immutable.List(payload.labels);

      return state.merge({ customReports, builtInReports, labels });
    },
    done: state => state.set('reportsLoaded', true),
  }),
  [actions.loadGroupParams]: async({
    success: setFullPayload('groupParams'),
  })
});
