import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { AppContainer } from 'react-hot-loader';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import { IntlProvider } from 'react-intl';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import bootstrapDemo from './Modules/Application/Actions/bootstrapActions';
import DpAppContainer from './Modules/Application/Components/DpAppContainer';
import { repositoriesConfig } from './DAL/config';
import AdminReducers from './DemoApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';

window.DP_LOCALE = 'en';
window.DP_LANG = {
  'cloud.demo_expired.login_title': 'Your free trial has ended',
  'feedback.nav.title':             'Feedback',
  'feedback.nav.tabs.status':       'Status'
};

class DemoApp {
  run = () => {
    DemoApp.start();
  };

  static start() {
    /* global __DEV__ */
    window.DP_DEV_MODE = __DEV__;
    const store = DemoApp.createStore();
    store.dispatch(bootstrapDemo()).then(() => {
      ReactDOM.render(
        <AppContainer>
          <Provider store={store}>
            <IntlProvider locale={window.DP_LOCALE} messages={window.DP_LANG}>
              <DpAppContainer />
            </IntlProvider>
          </Provider>
        </AppContainer>,
        document.getElementById('app')
      );
    });
  }

  static createStore(initialState = {}) {
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

    const reducer = combineReducerHierarchy(Object.assign({}, AdminReducers, AppReducers), legacyReducerBuilder);
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

    return makeStore(reducer, initialState, window.devToolsExtension ? window.devToolsExtension() : f => f);
  }

}
export default DemoApp;
