import { createAction } from 'DeskPRO/Component/Ampliflux';

export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_FIND_ALL_STATE = 'DESKPRO_APPSTORE_FIND_ALL_STATE';
export const DESKPRO_APPSTORE_GET_STATE = 'DESKPRO_APPSTORE_GET_STATE';
export const DESKPRO_APPSTORE_APP_MOUNTED = 'DESKPRO_APPSTORE_APP_MOUNTED';
export const DESKPRO_APPSTORE_APPCONTEXT_CREATED = 'DESKPRO_APPSTORE_APPCONTEXT_CREATED';

/**
 *  creates an action that signals a context ( like a ticket pane ) supporting deskpro apps
 */
export const appContextCreated = createAction(
  DESKPRO_APPSTORE_APPCONTEXT_CREATED,
  (appContext, domNodeList, eventBus) => new Promise((resolve, reject) => {
    setTimeout(() => {
      resolve(appContext);
      eventBus.dispatch(DESKPRO_APPSTORE_APPCONTEXT_CREATED, appContext, domNodeList);
    }, 2000)
  })
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
  (target, eventBus) => new Promise((resolve, reject) => {
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
  (appId, api, callback, eventBus) =>  api.sendGet(`DP_API/apps/${appId}/state`)
    .then(httpResponse => httpResponse.data)
    .then(state => { callback(appId, state); return state; } )
    .then(state => eventBus.dispatch(DESKPRO_APPSTORE_FIND_ALL_STATE, appId, state))
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
