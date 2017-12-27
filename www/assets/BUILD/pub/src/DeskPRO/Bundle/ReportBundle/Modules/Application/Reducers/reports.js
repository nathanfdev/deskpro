import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as actions from '../Actions/reportActions';

const initialState = {
  reportsLoaded: false,
  currentReport: Immutable.fromJS({}),
  groupParams:   Immutable.fromJS({}),
  reportLoading: true,
  labels:        []
};

export default createReducer(initialState, {
  [actions.loadReport]: async({
    start:   state => state.set('reportLoading', true),
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload)),
    done:    state => state.set('reportLoading', false)
  }),
  [actions.runReport]: async({
    start:   state => state.set('reportLoading', true),
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload)),
    done:    state => state.set('reportLoading', false)
  }),
  [actions.newReport]: async({
    success: (state, payload) => state.set('currentReport', Immutable.fromJS(payload))
  }),
  [actions.reportsLoaded]:   state => state.set('reportsLoaded', true),
  [actions.loadGroupParams]: async({
    success: setFullPayload('groupParams')
  }),
  [actions.addLabels]: (state, payload) => {
    const labels = Object.values(state.get('labels').toJS().map(label => label.label));

    let newLabels;
    if (Array.isArray(payload)) {
      newLabels = payload;
    } else {
      newLabels = [payload];
    }

    newLabels.forEach((label) => {
      if (labels.indexOf(label) === -1) {
        labels.push(label);
      }
    });

    return state.set('labels', Immutable.fromJS(labels.map(label => ({ label }))));
  }
});
