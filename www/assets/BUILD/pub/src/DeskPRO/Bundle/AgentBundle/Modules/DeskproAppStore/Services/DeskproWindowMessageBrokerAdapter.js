class DeskproWindowMessageBrokerAdapter
{
  /**
   * @return {string}
   */
  static get EVENTPATTERN()
  {
    return 'agent.ui.tabinit.*';
  }

  /**
   * @param {DeskPRO.MessageBroker} messageBroker
   * @returns {function(*=, *=)}
   */
  static registerListener (messageBroker)
  {
      return (store, domScanner) => {
        const listener = this.createMessageListener(store, domScanner);
        const pattern  = this.EVENTPATTERN;
        messageBroker.addMessageListener(pattern, listener);
      } ;
  }

  /**
   * @param {ReduxActionDispatcher} reduxActionDispatcher
   * @param {Function} domScanner
   * @returns {function(*, ...[*])}
   */
  static createMessageListener (reduxActionDispatcher, domScanner)
  {
    return (page, ...args) => {
      if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {

        const { TYPENAME, fragmentElement, pageUid } = page;
        // TODO we have to select what typenames we can handle

        const metadata = page.getMetaData(TYPENAME);
        if (!metadata || !metadata.id) {
          return false;
        }

        const domNodeList = domScanner( fragmentElement.get() );
        if (domNodeList.length === 0) { //no containers to be found in this page fragment dom
          return false;
        }

        reduxActionDispatcher.dispatchLoadPageFragmentApps(page);
      }
    };
  }
}

export default DeskproWindowMessageBrokerAdapter;
