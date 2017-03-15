import DeskproAppContainerLoader from './DeskproAppContainerLoader';
import ContainerDOMNodeSelector from './ContainerDOMNodeSelector';
import { filterAppConfig } from '../Selectors/Main';

class DeskproAppContainerLoaderFactory
{
  /**
   * @param {Object} store
   * @returns {DeskproAppContainerLoader}
   */
  static fromReduxStore(store)
  {
    const state = store.getState();
    const appsConfig = filterAppConfig(state).toJS();

    return this.fromJS(appsConfig);
  }

  /**
   * @param {Map} appConfig
   * @returns {DeskproAppContainerLoader}
   */
  static fromJS(appConfig)
  {
    const domSelector = new ContainerDOMNodeSelector('data-deskproapp');
    return new DeskproAppContainerLoader(domSelector, appConfig);
  }
}

export default DeskproAppContainerLoaderFactory;
