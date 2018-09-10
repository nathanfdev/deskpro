import { ManifestParsers } from './ManifestParsers';

export class ManifestLoader {
  /**
   * @param {DpApi} apiClient
   */
  constructor(apiClient)  {
    this.apiClient = apiClient;
  }

  loadAll()  {
    return this.apiClient.sendGet('DP_API/apps?include=app&isInstalled=true&isDev=false')
      .then(response => response.data)
      .then(({ data, linked }) => data.map(item => ManifestParsers.parseManifestResponseBody({ data: item, linked })))
    ;
  }

  loadPackage(appName)  {
    return this.apiClient.sendGet(`DP_API/apps/packages/${appName}`).then(response => response.data.data);
  }

  loadApp(appName)  {
    return this.apiClient.sendGet(`DP_API/apps/${appName}?include=app`)
      .then(response => response.data)
      .then(ManifestParsers.parseManifestResponseBody)
      ;
  }

  loadDev(endpoint)  {
    const url = `${endpoint}/manifest.json`;
    return this.apiClient.sendGet(url).then(response => response.data);
  }
}
