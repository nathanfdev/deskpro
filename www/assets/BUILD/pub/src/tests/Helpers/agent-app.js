import { loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import { createStore, applyMiddleware, compose } from 'redux';
import AgentReducers from 'DeskPRO/Bundle/AgentBundle/AgentApp_Reducers';
import AppReducers from 'DeskPRO/Bundle/AppBundle/AppApp_Reducers';

let reduxStore;

export function dispatchInAgent(action, state) {

  // Bootstrap DAL
  loadRepositoriesConfig(repositoriesConfig);

  // Create redux store
  const legacyReducerBuilder = function(reducer) {
    if (reducer.isAmplifluxReducer) {
      const rInst = new reducer();
      return rInst.compile();
    }

    return reducer;
  };

  const reducer = combineReducerHierarchy(Object.assign({}, AgentReducers, AppReducers), legacyReducerBuilder);
  const middleware = applyMiddleware(
    ampMiddleware.actionThunkMiddleware,
    ampMiddleware.redispatchDsaPayload,
    ampMiddleware.promiseMiddleware
  );
  const makeStore = compose(middleware)(createStore);
  reduxStore = makeStore(reducer, state);

  return reduxStore.dispatch(action)
}

export function getReduxState() {
  return reduxStore.getState();
}