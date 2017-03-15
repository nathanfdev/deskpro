import DeskproAppContainerLoaderFactory from './Services/DeskproAppContainerLoaderFactory';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import EventBus from './Services/EventBus';

import { DESKPRO_APPSTORE_APPCONTEXT_CREATED } from './Actions/Actions'

class DeskproAppStore
{
  /**
   * Initializes the components of the app store in the deskpro context
   *
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {Object} reduxStore
   */
  static bootstrap(messageBroker, reduxStore)
  {
    //register a listener for any loaded content so we can instantiate any apps
    const containerLoader = DeskproAppContainerLoaderFactory.fromReduxStore(reduxStore);
    const eventBus = new EventBus();

    eventBus.addEventListener(
      DESKPRO_APPSTORE_APPCONTEXT_CREATED
      , (context, domNodeList) => {
          containerLoader.load(domNodeList, context, reduxStore)
        }
      );

    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxStore, containerLoader, eventBus);
  }
}

export default DeskproAppStore;
