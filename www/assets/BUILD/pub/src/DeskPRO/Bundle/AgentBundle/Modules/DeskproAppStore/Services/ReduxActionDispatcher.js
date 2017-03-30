import * as Actions from '../Actions/Actions'

class ReduxActionDispatcher
{
  /**
   * @param reduxStore
   * @param {DpApi} api
   * @return {ReduxActionDispatcher}
   */
  static fromReduxStore(reduxStore, api)
  {
    return new ReduxActionDispatcher(api, action => reduxStore.dispatch(action));
  }

  /**
   * @param {DpApi} api
   * @param {Function} reduxDispatch
   */
  constructor(api, reduxDispatch) {
    this.api = api;
    this.reduxDispatch = reduxDispatch;
  }

  dispatchMountPageFragmentContainers = () =>
  {
    const { reduxDispatch } = this;
    reduxDispatch(Actions.mountPageFragmentContainers());
  };

  /**
   * @param {DeskPRO.Agent.PageFragment.Basic} page
   */
  dispatchLoadPageFragmentApps = (page) =>
  {
    const { reduxDispatch } = this;
    const action = Actions.loadPageFragmentApps(page);
    reduxDispatch(action);
  };

  dispatchLoadApps = () =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.loadApps(api));
  };

  dispatchAppMounted = (target) =>
  {
    const { reduxDispatch } = this;
    const action = Actions.appMounted(target);
    reduxDispatch(action);
  };

  dispatchFindAppState = (appId, callback) =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.findAppState(appId, api, callback));
  };

  dispatchGetAppState = (appId, name, scope, callback) =>
  {
    const { reduxDispatch, api} = this;
    reduxDispatch(Actions.getAppState(appId, name, scope, callback, api));
  };

  dispatchSaveState = (appId, state, callback) =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.saveAppState(appId, state, callback, api));
  };

  dispatchDeleteState = (appId, name, callback) =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.deleteAppState(appId, name, callback, api));
  };
}

export default ReduxActionDispatcher;
