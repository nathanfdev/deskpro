import { createAction } from 'DeskPRO/Component/Ampliflux';
import * as WidgetDOM from '../WidgetDOM';
import { Context } from '../Domain/Context';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_LOAD_DEV_APPS = 'DESKPRO_APPSTORE_LOAD_DEV_APPS';
export const DESKPRO_APPSTORE_APP_MOUNTED = 'DESKPRO_APPSTORE_APP_MOUNTED';

export const DESKPRO_APPSTORE_STATE_FIND = 'DESKPRO_APPSTORE__STATE_FIND';
export const DESKPRO_APPSTORE_STATE_GET = 'DESKPRO_APPSTORE_STATE_GET';
export const DESKPRO_APPSTORE_STATE_DELETE = 'DESKPRO_APPSTORE_STATE_DELETE';

export const DESKPRO_APPSTORE_USER_GET = 'DESKPRO_APPSTORE_USER_GET';


/**
* @param {DeskPRO.Agent.PageFragment.Basic} page
* @return Array<Context>
*/
function pageToContext(page) {
  try {
    const foundNodes = WidgetDOM.container.findAllFromList(page.wrapper.get());
    if (foundNodes.length === 0) { return []; }

    // make sure all dom nodes have an id
    WidgetDOM.container.ensureContainerIds(foundNodes);

    // extract tab props
    const { TYPENAME: type } = page;
    const metadata = page.getMetaData(type);
    const routeUrl = page.getMetaData('routeUrl');
    const tab = window.DeskPRO_Window.TabBar.findTabByRouteUrl(routeUrl);
    const tabProps = { type, entityId: metadata.id, tabId: tab.id };

    // extract container props
    const attributeToProp = ({ id, 'data-deskproapp': locationId }) => ({ id, locationId });
    const containerPropList = WidgetDOM.container.extractConfigurationFromList(foundNodes, attributeToProp);

    // create context list
    return containerPropList.map(containerProps => new Context(Object.assign({}, tabProps, containerProps)));
  } catch (e) {
    console.log('failed to extract app context from page fragment', e);
    return [];
  }
}

export const loadPageFragmentApps = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, pageToContext);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadDevApp = createAction(
  DESKPRO_APPSTORE_LOAD_DEV_APPS,
  // TODO put this together with the other constants
  (api, manifestUrl) =>  api.sendGet(manifestUrl).then(httpResponse => httpResponse.data)
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadApps = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  api =>  api.sendGet('DP_API/apps?include=app')
    .then(httpResponse => httpResponse.data)
);

/**
 * creates an action dispatched when an app has been mounted
 */
export const appMounted = createAction(
  DESKPRO_APPSTORE_APP_MOUNTED,
  // TODO: handle reject case as well, new Promise((resolve, reject) => {
  target => new Promise((resolve) => {
    setTimeout(() => {
      const payload = { target };
      resolve(payload);
      // TODO do we need the event bus for deskpro
    }, 2000);
  })
);

