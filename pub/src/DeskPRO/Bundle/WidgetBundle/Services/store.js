import AppReducers from '../WidgetApp_Reducers.js';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';

const reducer = combineReducerHierarchy(AppReducers);
const middleware = applyMiddleware(
  ampMiddleware.intervalMiddleware,
  ampMiddleware.timeoutMiddleware,
  ampMiddleware.actionThunkMiddleware,
  ampMiddleware.redispatchDsaPayload,
  ampMiddleware.promiseMiddleware,
  ampMiddleware.loggerMiddleware
);

const makeStore = compose(middleware)(createStore);
export default makeStore(reducer);
