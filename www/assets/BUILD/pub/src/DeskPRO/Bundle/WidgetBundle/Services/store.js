import WidgetAppReducers from '../WidgetApp_Reducers.js';
import AppReducers from '../../AppBundle/AppApp_Reducers';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';

const reducer = combineReducerHierarchy(Object.assign({}, WidgetAppReducers, AppReducers));
const middleware = applyMiddleware(
  ampMiddleware.intervalMiddleware,
  ampMiddleware.timeoutMiddleware,
  ampMiddleware.actionThunkMiddleware,
  ampMiddleware.redispatchDsaPayload,
  ampMiddleware.promiseMiddleware,
  ampMiddleware.loggerMiddleware
);

const makeStore = compose(middleware)(createStore);
export const store = makeStore(reducer);
