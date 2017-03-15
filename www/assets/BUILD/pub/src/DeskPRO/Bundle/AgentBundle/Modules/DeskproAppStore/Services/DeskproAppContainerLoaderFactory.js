import DeskproAppContainerLoader from './DeskproAppContainerLoader';
import ContainerDOMNodeSelector from './ContainerDOMNodeSelector';
import { filterInstanceConfig } from '../Selectors/Main';

class DeskproAppContainerLoaderFactory
{
  /**
   * @param {Object} store
   * @param {Array<String} validTargets
   * @returns {DeskproAppContainerLoader}
   */
  static fromReduxStore(store, validTargets)
  {
    const state = store.getState();
    const appsConfig = filterInstanceConfig(state);

    return this.fromJS(appsConfig, validTargets);
  }

  /**
   * @param {Map} appConfig
   * @param {Array<String} validTargets
   * @returns {DeskproAppContainerLoader}
   */
  static fromJS(appConfig, validTargets)
  {
    const domSelector = new ContainerDOMNodeSelector('data-deskproapp');
    return new DeskproAppContainerLoader(domSelector, appConfig, validTargets);
  }
}

export default DeskproAppContainerLoaderFactory;
