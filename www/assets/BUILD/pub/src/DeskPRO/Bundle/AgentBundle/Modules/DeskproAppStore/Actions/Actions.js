import { createAction } from 'DeskPRO/Component/Ampliflux';

export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
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
  (target, parentComponent, eventBus) => new Promise((resolve, reject) => {
    setTimeout(() => {
      const payload = {target, parentComponent};
      resolve(payload);
      // TODO do we need the event bus for deskpro
    }, 2000)
  })
);
