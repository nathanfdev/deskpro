import { createAction } from 'DeskPRO/Component/Ampliflux';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_MOUNT_PAGE_FRAGMENT_CONTAINERS = 'DESKPRO_APPSTORE_MOUNT_PAGE_FRAGMENT_CONTAINERS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_FIND_ALL_STATE = 'DESKPRO_APPSTORE_FIND_ALL_STATE';
export const DESKPRO_APPSTORE_GET_STATE = 'DESKPRO_APPSTORE_GET_STATE';
export const DESKPRO_APPSTORE_APP_MOUNTED = 'DESKPRO_APPSTORE_APP_MOUNTED';


/**
* @param {DeskPRO.Agent.PageFragment.Basic} page
*/
function pageToContext(page) {
  const { TYPENAME, pageUid } = page;
  const metadata = page.getMetaData(TYPENAME);

  return {
    id: pageUid,
    objectId: metadata.id,
    objectType: TYPENAME,
    object: {
      id: metadata.id,
      type: TYPENAME
    },
    page: {
      pageUid: page.pageUid,
      routeUrl: page.getMetaData('routeUrl')
    }
  }
}
export const loadPageFragmentApps = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, pageToContext);

export const mountPageFragmentContainers = createAction(DESKPRO_APPSTORE_MOUNT_PAGE_FRAGMENT_CONTAINERS, () => {

});

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadApps = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  api =>  api.sendGet('DP_API/apps')
    .then(httpResponse => httpResponse.data)
);

/**
 * creates an action dispatched when an app has been mounted
 */
export const appMounted = createAction(
  DESKPRO_APPSTORE_APP_MOUNTED,
  (target) => new Promise((resolve, reject) => {
    setTimeout(() => {
      const payload = {target};
      resolve(payload);
      // TODO do we need the event bus for deskpro
    }, 2000)
  })
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const findAllAppState = createAction(
  DESKPRO_APPSTORE_FIND_ALL_STATE,
  (appId, api, callback) =>  api.sendGet(`DP_API/apps/${appId}/state`)
    .then(httpResponse => httpResponse.data)
    .then(state => { callback(appId, state); return state; } )
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const getAppState = createAction(
  DESKPRO_APPSTORE_GET_STATE,
  (appId, name, scope, api, callback) =>  api.sendGet(`DP_API/apps/${appId}/state/${name}/${scope}`)
    .then(httpResponse => httpResponse.data)
    .then(state => { callback(appId, state); return state; } )
    .catch(httpResponse => httpResponse.data )
    .then(data => {

      if (404 === data.status) {
        callback(appId, null);
        return;
      }
       throw new Error('Failed to retrieve app state');
    })
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const saveState = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  (appId, state, callback, api) =>  api.sendPost(`DP_API/apps/${appId}/state`, state)
    .then(httpResponse => httpResponse.data)
    .then(response => { callback(appId, state); return state; } )
);
