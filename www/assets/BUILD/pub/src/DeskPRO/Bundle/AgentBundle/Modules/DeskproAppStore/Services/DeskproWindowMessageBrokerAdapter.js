import { appContextCreated } from '../Actions/Actions';

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
      return (store, containerLoader, eventBus) => {
        const listener = this.createMessageListener(store, containerLoader, eventBus);
        const pattern  = this.EVENTPATTERN;
        messageBroker.addMessageListener(pattern, listener);
      } ;
  }

  /**
   * @param {Object} store
   * @param {DeskproAppContainerLoader} containerLoader
   * @param {EventBus} eventBus
   * @returns {function(*, ...[*])}
   */
  static createMessageListener (store, containerLoader, eventBus)
  {
    return (page, ...args) => {
      if (page instanceof window.DeskPRO.Agent.PageFragment.Basic) {

        const { TYPENAME, fragmentElement, pageUid } = page;
        const metadata = page.getMetaData(TYPENAME);
        // TODO we have to select what typenames we can handle

        // console.log('metadata: ', metadata, TYPENAME);
        if (!metadata || !metadata.id) {
          return false;
        }

        const domNodeList = containerLoader.findContainerDOMNodeList( fragmentElement.get() );
        if (domNodeList.length === 0) { //no containers to be found in this page fragment dom
          return false;
        }

        const appContext = { id: pageUid, objectId: metadata.id, objectType: TYPENAME };
        store.dispatch(appContextCreated(appContext, domNodeList, eventBus));
      }
    };
  }
}

export default DeskproWindowMessageBrokerAdapter;
