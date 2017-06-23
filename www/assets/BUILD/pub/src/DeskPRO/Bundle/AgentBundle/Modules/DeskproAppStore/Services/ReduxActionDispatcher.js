import * as Actions from '../Actions/Actions';

class ReduxActionDispatcher {
  /**
   * @param reduxStore
   * @param {DpApi} api
   * @return {ReduxActionDispatcher}
   */
  static fromReduxStore(reduxStore, api)  {
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
   * @param {DeskPRO.Agent.PageFragment.Basic|Array<DeskPRO.Agent.PageFragment.Basic>} page
   */
  dispatchLoadPageFragmentApps = (page) =>  {
    const list = page instanceof Array ? page : [page];

    const { reduxDispatch } = this;
    const action = Actions.loadPageFragmentApps(list, window.location);
    reduxDispatch(action);
  };

  dispatchLoadApps = () =>  {
    const { reduxDispatch, api } = this;
    reduxDispatch(Actions.loadApps(api));
  };

  dispatchAppMounted = (target) =>  {
    const { reduxDispatch } = this;
    const action = Actions.appMounted(target);
    reduxDispatch(action);
  };

}

export default ReduxActionDispatcher;
