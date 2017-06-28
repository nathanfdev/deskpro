class DeskproWindowMessageBrokerAdapter {
  /**
   * @return {Array<String>}
   */
  static get EVENTPATTERNS()  {
    return ['agent.ui.tabinit.*'];
  }

  /**
   * @param {DeskPRO.MessageBroker} messageBroker
   * @returns {function(*=, *=)}
   */
  static registerListener(messageBroker)  {
    return (store, domScanner) => {
      const listener = this.createMessageListener(store, domScanner);
      for (const pattern of this.EVENTPATTERNS) {
        messageBroker.addMessageListener(pattern, listener);
      }
    };
  }

  /**
   * @param {ReduxActionDispatcher} reduxActionDispatcher
   * @returns {function(*, ...[*])}
   */
  static createMessageListener(reduxActionDispatcher)  {
    return (page, ...args) => { // eslint-disable-line no-unused-vars
      if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {
        const { TYPENAME } = page;

        const metadata = page.getMetaData(TYPENAME);
        if (!metadata || !metadata.id) {
          return false;
        }

        reduxActionDispatcher.dispatchLoadPageFragmentApps(page);
        return true;
      }

      return false;
    };
  }
}

export default DeskproWindowMessageBrokerAdapter;
