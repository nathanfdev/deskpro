import { createAction } from 'DeskPRO/Component/Ampliflux';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_MOUNT_PAGE_FRAGMENT_CONTAINERS = 'DESKPRO_APPSTORE_MOUNT_PAGE_FRAGMENT_CONTAINERS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_LOAD_DEV_APPS = 'DESKPRO_APPSTORE_LOAD_DEV_APPS';
export const DESKPRO_APPSTORE_APP_MOUNTED = 'DESKPRO_APPSTORE_APP_MOUNTED';

export const DESKPRO_APPSTORE_STATE_FIND = 'DESKPRO_APPSTORE__STATE_FIND';
export const DESKPRO_APPSTORE_STATE_GET = 'DESKPRO_APPSTORE_STATE_GET';
export const DESKPRO_APPSTORE_STATE_DELETE = 'DESKPRO_APPSTORE_STATE_DELETE';


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
export const loadDevApp = createAction(
  DESKPRO_APPSTORE_LOAD_DEV_APPS,
  //TODO put this together with the other constants
  (api, manifestUrl) =>  api.sendGet(manifestUrl).then(httpResponse => httpResponse.data)
);

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
export const findAppState = createAction(
  DESKPRO_APPSTORE_STATE_FIND,
  (appId, callback, api) =>  api.sendGet(`DP_API/apps/${appId}/state`)
    .then(httpResponse => httpResponse.data)
    .then(state => { callback(appId, state); return state; } )
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const getAppState = createAction(
  DESKPRO_APPSTORE_STATE_GET,
  (appId, name, scope, callback, api) =>  api.sendGet(`DP_API/apps/${appId}/state/${name}/${scope}`)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) {
        return httpResponse;
      }

      if (404 === httpResponse.data.status) {
        return null;
      }

      return new Error('failed to get app state');
    })
    .then(data => {
      if (data instanceof Error) {
        callback(appId, null);
        throw data;
      }
      callback(appId, data);
      return data;
    })
);

/**
 * creates an action that will delete a state variable by name
 */
export const deleteAppState = createAction(
  DESKPRO_APPSTORE_STATE_DELETE,
  (appId, name, callback, api) =>  api.sendDelete(`DP_API/apps/${appId}/state/${name}`)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) {
        return httpResponse;
      }

      if (404 === httpResponse.data.status) {
        return null;
      }

      return new Error('failed to delete app state');
    })
    .then(data => {
      if (data instanceof Error) {
        callback(appId, null);
        throw data;
      }
      callback(appId, data);
      return data;
    })
);

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const saveAppState = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  (appId, state, callback, api) =>  api.sendPost(`DP_API/apps/${appId}/state`, state)
    .then(httpResponse => httpResponse.data)
    .then(response => { callback(appId, state); return state; } )
);

export const updateAppState = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  (appId, stateName, state, callback, api) =>  api.sendPut(`DP_API/apps/${appId}/state/${stateName}`, state)
    .then(httpResponse => httpResponse.data)
    .then(response => { callback(appId, state); return state; } )
);

export const createAppState = createAction(
  DESKPRO_APPSTORE_LOAD_APPS,
  (appId, state, callback, api) =>  api.sendPost(`DP_API/apps/${appId}/state`, state)
    .then(httpResponse => httpResponse.data)
    .then(response => { callback(appId, state); return state; } )
);
