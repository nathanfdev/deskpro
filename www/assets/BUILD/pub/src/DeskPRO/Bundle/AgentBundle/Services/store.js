import { createStore, applyMiddleware, compose } from 'redux';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import * as ampMiddleware from 'Ampliflux/middleware';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import { combineReducerHierarchy } from 'Ampliflux';
import AgentReducers from '../AgentApp_Reducers';
import AppReducers from '../../AppBundle/AppApp_Reducers';

// Bootstrap API and DAL
setApi(api);
loadRepositoriesConfig(repositoriesConfig);

// This builder calls compile on old-style reducers
// created via the Reducer class
const legacyReducerBuilder = (reducer) => {
  if (reducer.isAmplifluxReducer) {
    const rInst = new reducer();
    return rInst.compile();
  }

  return reducer;
};

const reducer = combineReducerHierarchy(Object.assign({}, AgentReducers, AppReducers), legacyReducerBuilder);
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
