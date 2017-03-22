import * as Actions from '../Actions/Actions'


class ReduxActionDispatcher
{
  /**
   * @param {DpApi} api
   * @param {EventBus} eventBus
   * @param {Function} reduxDispatch
   */
  constructor(api, eventBus, reduxDispatch) {
    this.api = api;
    this.eventBus = eventBus;
    this.reduxDispatch = reduxDispatch;
  }

  dispatchAppContextCreated = (appContext, domNodeList) =>
  {
    const { reduxDispatch, eventBus } = this;
    reduxDispatch(Actions.appContextCreated(appContext, domNodeList, eventBus));
  };

  dispatchLoadApps = () =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.loadApps(api));
  };

  dispatchAppMounted = (target) =>
  {
    const { reduxDispatch, eventBus } = this;
    const action = Actions.appMounted(target, eventBus);
    reduxDispatch(action);
  };

  dispatchFindAllAppState = (appId, callback) =>
  {
    const { reduxDispatch, api, eventBus } = this;
    reduxDispatch(Actions.findAllAppState(appId, api, callback, eventBus));
  };

  dispatchGetAppState = (appId, name, scope, callback) =>
  {
    const { reduxDispatch, api, eventBus } = this;
    reduxDispatch(Actions.getAppState(appId, name, scope, api, callback));
  };

  dispatchSaveState = (appId, state, callback) =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.saveState(appId, state, callback, api));
  }
}

export default ReduxActionDispatcher;
