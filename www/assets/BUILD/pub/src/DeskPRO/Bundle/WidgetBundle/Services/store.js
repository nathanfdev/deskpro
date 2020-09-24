import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import WidgetAppReducers from '../WidgetApp_Reducers';
import AppReducers from '../../AppBundle/AppApp_Reducers';

// eslint-disable-next-line no-underscore-dangle
const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || window.parent.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const reducer = combineReducerHierarchy(Object.assign({}, WidgetAppReducers, AppReducers));
const middleware = applyMiddleware(
  ampMiddleware.intervalMiddleware,
  ampMiddleware.timeoutMiddleware,
  ampMiddleware.actionThunkMiddleware,
  ampMiddleware.redispatchDsaPayload,
  ampMiddleware.promiseMiddleware,
  ampMiddleware.loggerMiddleware
);

const makeStore = composeEnhancers(middleware)(createStore);
export const store = makeStore(reducer);
