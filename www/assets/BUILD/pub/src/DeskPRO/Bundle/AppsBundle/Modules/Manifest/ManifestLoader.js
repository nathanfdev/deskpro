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

  loadPackage(app)  {
    // double encoded as symfony decodes the uri before matching the routes so forward slashes which are part of the
    // name will influence the matching algorithm
    const encodedName = encodeURIComponent(encodeURIComponent(app));
    return this.apiClient.sendGet(`DP_API/apps/packages/${encodedName}`).then(response => response.data.data);
  }

  loadApp(app)  {
    // double encoded as symfony decodes the uri before matching the routes so forward slashes which are part of the
    // name will influence the matching algorithm
    const encodedName = encodeURIComponent(encodeURIComponent(app));
    return this.apiClient.sendGet(`DP_API/apps/${encodedName}?include=app`)
      .then(response => response.data)
      .then(ManifestParsers.parseManifestResponseBody)
      ;
  }

  loadDev(endpoint)  {
    const url = `${endpoint}/manifest.json`;
    return this.apiClient.sendGet(url).then(response => response.data);
  }
}
