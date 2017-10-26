import { createAction } from 'DeskPRO/Component/Ampliflux';
import { extractPageContextProps } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import { ManifestLoader } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_LOAD_CONFIG = 'DESKPRO_APPSTORE_LOAD_CONFIG';
export const DESKPRO_APPSTORE_API_TOKEN = 'DESKPRO_APPSTORE_API_TOKEN';

/**
 * @param {AppsConfig} config
 */
const loadAppstoreConfigHandler = ({ config }) => Promise.resolve({ config: config.toJS() });
export const loadAppstoreConfig = createAction(DESKPRO_APPSTORE_LOAD_CONFIG, loadAppstoreConfigHandler);

/**
 * @param {Array<DeskPRO.Agent.PageFragment.Basic>} pageList
 * @param {AppsConfig} config
 * @param {Location} location
 */
const extractContextsFromPageFragment = (pageList, config, location) => {
  const contexts = pageList.map(page => extractPageContextProps(page, location))
    .reduce((acc, contextList) => acc.concat(contextList), [])
  ;
  return { config, contexts };
};
export const loadContextsFromPageFragments = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, extractContextsFromPageFragment);
export const unloadContextsFromPageFragments = createAction(DESKPRO_APPSTORE_UNLOAD_PAGE_FRAGMENT_APPS, extractContextsFromPageFragment);

/**
 * @param {DpApi} api
 * @param {AppsConfig} config
 */
const loadAppsHandler = ({ api, config }) => {
  const isDev = config.environment === 'development';
  const loader = new ManifestLoader(api);

  if (isDev) {
    return loader.loadDev(config.endpoint).then(manifests => ({ config, manifests: [manifests] }));
  }

  return loader.loadAll().then(manifests => ({ config, manifests }));
};
/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadApps = createAction(DESKPRO_APPSTORE_LOAD_APPS, loadAppsHandler);

const loadApiTokenHandler = ({ api, config }) => {
  const onRetrieveApiTokenSuccess = (response) => {
    const { token } = response.data.data;
    return { config, token };
  };

  return api.sendGet('DP_API/api_tokens/session').then(onRetrieveApiTokenSuccess);
};
export const loadApiToken = createAction(DESKPRO_APPSTORE_API_TOKEN, loadApiTokenHandler);
