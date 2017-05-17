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

  dispatchSetAppState = (appId, state, callback) =>
  {
    const { reduxDispatch, api } = this;
    const { name } = state;
    reduxDispatch(Actions.setAppState(appId, name, state, callback, api));
  };

  dispatchDeleteAppState = (appId, name, callback) =>
  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.deleteAppState(appId, name, callback, api));
  };


  dispatchGetUser = (appId, callback) =>
  {
    const { reduxDispatch, api} = this;
    reduxDispatch(Actions.getUser(appId, callback, api));
  };
}

export default ReduxActionDispatcher;
