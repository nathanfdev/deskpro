import { createAction } from 'DeskPRO/Component/Ampliflux';
import { createContextsFromPage } from '../Services';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_LOAD_DEV_APPS = 'DESKPRO_APPSTORE_LOAD_DEV_APPS';
export const DESKPRO_APPSTORE_APP_MOUNTED = 'DESKPRO_APPSTORE_APP_MOUNTED';

/**
 * @param {Array<DeskPRO.Agent.PageFragment.Basic>} pageList
 * @param {Location} location
 */
const loadPageFragmentAppsHandler = (pageList, location) => pageList
  .map(page => createContextsFromPage(page, location))
  .reduce((acc, contextList) => acc.concat(contextList), [])
;
export const loadPageFragmentApps = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, loadPageFragmentAppsHandler);

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

