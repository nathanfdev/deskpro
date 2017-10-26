const readAppManifest = (data, linked) => {
  const { application_id: appId, id, targets, name: title } = data;
  const manifest = JSON.parse(JSON.stringify(linked.app[appId].manifest));
  return { ...manifest, application_id: appId, id, targets, title };
};


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
      .then(({ data, linked }) => data.map(manifest => readAppManifest(manifest, linked)))
    ;
  }

  loadApp(app)  {
    // double encoded as symfony decodes the uri before matching the routes so forward slashes which are part of the
    // name will influence the matching algorithm
    const encodedName = encodeURIComponent(encodeURIComponent(app));
    return this.apiClient.sendGet(`DP_API/apps/${encodedName}?include=app`)
      .then(response => response.data)
      .then(({ data, linked }) => readAppManifest(data, linked))
    ;
  }

  loadDev(endpoint)  {
    const url = `${endpoint}/manifest.json`;
    return this.apiClient.sendGet(url).then(response => response.data);
  }
}
