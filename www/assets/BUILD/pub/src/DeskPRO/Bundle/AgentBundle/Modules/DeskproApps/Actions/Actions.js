import { createAction } from 'DeskPRO/Component/Ampliflux';
import { contexts } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import { ManifestLoader } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_LOAD_CONFIG = 'DESKPRO_APPSTORE_LOAD_CONFIG';
export const DESKPRO_APPSTORE_API_TOKEN = 'DESKPRO_APPSTORE_API_TOKEN';
export const DESKPRO_APPSTORE_RELOAD_APP = 'DESKPRO_APPSTORE_RELOAD_APP';

/**
 * @param {Array<DeskPRO.Agent.PageFragment.Basic>} pageList
 * @param {AppsConfig} config
 * @param {Location} location
 */
const extractContextsFromPageFragment = (pageList, config, location) => {
  const extracted = pageList.map(page => contexts.extractPageContextProps(page, location))
    .reduce((acc, contextList) => acc.concat(contextList), [])
  ;
  return { config, contexts: extracted };
};
export const loadContextsFromPageFragments = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, extractContextsFromPageFragment);
export const unloadContextsFromPageFragments = createAction(DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS, extractContextsFromPageFragment);

/**
 * @param {DpApi} api
 * @param {AppsConfig} config
 */
function loadAppsAction({ api, config }) {
  const isDev = config.environment === 'development';
  const loader = new ManifestLoader(api);

  if (isDev) {
    return loader.loadDev(config.endpoint).then(manifest => ({ config, apps: [{ manifest, settings: {} }] }));
  }

  return loader.loadAll().then(apps => ({ config, apps }));
}

/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadApps = createAction(DESKPRO_APPSTORE_LOAD_APPS, loadAppsAction);

/**
 * @param {{ appStatus:string, applicationId:string }} notification
 * @param {DpApi} api
 * @param {AppsConfig} config
 * @return {*}
 */
function reloadAction(notification, api, config) {
  return loadAppsAction({ api, config });
}
export const reloadApps = createAction(DESKPRO_APPSTORE_RELOAD_APP, reloadAction);

const loadApiTokenHandler = ({ api, config }) => {
  const onRetrieveApiTokenSuccess = (response) => {
    const { token } = response.data.data;
    return { config, token };
  };

  return api.sendGet('DP_API/api_tokens/session').then(onRetrieveApiTokenSuccess);
};
export const loadApiToken = createAction(DESKPRO_APPSTORE_API_TOKEN, loadApiTokenHandler);
