import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import ReportAppReducers from '../ReportApp_Reducers';
import AppReducers from '../../AppBundle/AppApp_Reducers';

const reducer = combineReducerHierarchy(Object.assign({}, ReportAppReducers, AppReducers));
const middleware = applyMiddleware(
  ampMiddleware.timerMiddleware('startTime'),
  ampMiddleware.intervalMiddleware,
  ampMiddleware.timeoutMiddleware,
  ampMiddleware.actionThunkMiddleware,
  ampMiddleware.redispatchDsaPayload,
  ampMiddleware.guidMiddleware,
  ampMiddleware.promiseMiddleware,
  ampMiddleware.loggerMiddleware
);

const makeStore = compose(middleware)(createStore);

export default makeStore(reducer, {}, window.devToolsExtension ? window.devToolsExtension() : f => f);
