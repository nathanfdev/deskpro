import { loadContextsFromPageFragments, unloadContextsFromPageFragments } from '../Actions/Actions';

/**
 * @param {function} reduxDispatch
 * @param {AppsConfig} config
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param args
 */
const onTabInit = (reduxDispatch, config, page, ...args) => { // eslint-disable-line no-unused-vars
  if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {
    const { TYPENAME } = page;
    const metadata = page.getMetaData(TYPENAME);
    if (metadata && metadata.id) {
      const action = loadContextsFromPageFragments([page], config, window.location);
      reduxDispatch(action);
    }
  }
};

const onTabDestroy = (reduxDispatch, config, page, ...args)  => { // eslint-disable-line no-unused-vars
  if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {
    const { TYPENAME } = page;
    const metadata = page.getMetaData(TYPENAME);
    if (metadata && metadata.id) {
      const action = unloadContextsFromPageFragments([page], config, window.location);
      reduxDispatch(action);
    }
  }
};

export class DeskproWindowMessageBrokerAdapter {
  /**
   * @type {string}
   */
  static get EVENT_TAB_INIT()  {
    return 'agent.ui.tabinit.*';
  }

  /**
   * @type {string}
   */
  static get EVENT_TAB_BEFOREDESTROY()  {
    return 'agent.ui.tabbeforedestroy.*';
  }

  /**
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {AppsConfig} config
   * @returns {function(*=, *=)}
   */
  static registerListener(messageBroker, config)  {
    return (reduxDispatch) => {
      const onTabInitListener = onTabInit.bind(this, reduxDispatch, config);
      messageBroker.addMessageListener(DeskproWindowMessageBrokerAdapter.EVENT_TAB_INIT, onTabInitListener);

      const onTabDestroyListener = onTabDestroy.bind(this, reduxDispatch, config);
      messageBroker.addMessageListener(DeskproWindowMessageBrokerAdapter.EVENT_TAB_BEFOREDESTROY, onTabDestroyListener);
    };
  }
}
