import { createAction } from 'DeskPRO/Component/Ampliflux';
import { extractPageContextProps } from '../Services';

export const DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS = 'DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS';
export const DESKPRO_APPSTORE_LOAD_APPS = 'DESKPRO_APPSTORE_LOAD_APPS';
export const DESKPRO_APPSTORE_API_TOKEN = 'DESKPRO_APPSTORE_API_TOKEN';

/**
 * @param {Array<DeskPRO.Agent.PageFragment.Basic>} pageList
 * @param {DeskproAppStoreConfiguration} config
 * @param {Location} location
 */
const loadPageFragmentAppsHandler = (pageList, config, location) => {
  const contexts = pageList.map(page => extractPageContextProps(page, location))
    .reduce((acc, contextList) => acc.concat(contextList), [])
  ;
  return { config, contexts };
};
export const loadPageFragmentApps = createAction(DESKPRO_APPSTORE_LOAD_PAGE_FRAGMENT_APPS, loadPageFragmentAppsHandler);

/**
 * @param {DpApi} api
 * @param {DeskproAppStoreConfiguration} config
 */
const loadAppsHandler = (api, config) => {
  const isDev = config.environment === 'development';
  const manifestUrl = isDev ? `${config.endpoint}/manifest.json` : 'DP_API/apps?include=app';

  return api.sendGet(manifestUrl).then(httpResponse => ({ config, manifests: httpResponse.data }));
};
/**
 * creates an action that will load the app configuration for the current security principal
 */
export const loadApps = createAction(DESKPRO_APPSTORE_LOAD_APPS, loadAppsHandler);

const loadApiTokenHandler = (api, config) => {
  const onRetrieveApiTokenSuccess = (response) => {
    const { token } = response.data.data;
    return { config, token };
  };

  return api.sendGet('DP_API/api_tokens/session').then(onRetrieveApiTokenSuccess);
};
export const loadApiToken = createAction(DESKPRO_APPSTORE_API_TOKEN, loadApiTokenHandler);
