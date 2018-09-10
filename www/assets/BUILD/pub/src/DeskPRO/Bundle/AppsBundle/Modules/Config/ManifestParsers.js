export class ManifestParsers {
  static parseManifestResponseBody({ data, linked })  {
    const { application_id: appId, id, targets, name: title } = data;
    const manifest = JSON.parse(JSON.stringify(linked.app[appId].manifest));
    const settings = linked.app[appId].settings && typeof linked.app[appId].settings === 'object' ? JSON.parse(JSON.stringify(linked.app[appId].settings)) : {};
    return {
      manifest: { ...manifest, application_id: appId, id, targets, title },
      settings
    };
  }

}
