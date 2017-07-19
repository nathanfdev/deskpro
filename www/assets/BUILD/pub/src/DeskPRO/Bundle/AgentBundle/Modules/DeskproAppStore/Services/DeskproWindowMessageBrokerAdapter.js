import { loadPageFragmentApps } from '../Actions/Actions';

class DeskproWindowMessageBrokerAdapter {
  /**
   * @return {Array<String>}
   */
  static get EVENTPATTERNS()  {
    return ['agent.ui.tabinit.*'];
  }

  /**
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {DeskproAppStoreConfiguration} config
   * @returns {function(*=, *=)}
   */
  static registerListener(messageBroker, config)  {
    return (reduxDispatch) => {
      const listener = this.createMessageListener(reduxDispatch, config);
      for (const pattern of this.EVENTPATTERNS) {
        messageBroker.addMessageListener(pattern, listener);
      }
    };
  }

  /**
   * @param {function} reduxDispatch
   * @param {DeskproAppStoreConfiguration} config
   * @returns {function(*, ...[*])}
   */
  static createMessageListener(reduxDispatch, config)  {
    return (page, ...args) => { // eslint-disable-line no-unused-vars
      if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {
        const { TYPENAME } = page;
        const metadata = page.getMetaData(TYPENAME);
        if (metadata && metadata.id) {
          const action = loadPageFragmentApps([page], config, window.location);
          reduxDispatch(action);
          return true;
        }
      }

      return false;
    };
  }
}

export default DeskproWindowMessageBrokerAdapter;
