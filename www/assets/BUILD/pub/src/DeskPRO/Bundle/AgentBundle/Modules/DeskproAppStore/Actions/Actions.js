import { createAction } from 'DeskPRO/Component/Ampliflux';
import { config as appConfig } from './apps-config';

export const DESKPRO_APPSTORE_LOAD_APP_CONFIG = 'DESKPRO_APPSTORE_LOAD_APP_CONFIG';
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
 * creates an action that will load the app configuration for an agent
 */
export const loadAppConfig = createAction(
  DESKPRO_APPSTORE_LOAD_APP_CONFIG,
  () => new Promise((resolve, reject) => setTimeout(() => {resolve(appConfig)}, 2000))
);
// TODO use the api to load the app config

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
