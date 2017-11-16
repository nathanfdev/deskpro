export class ManifestParsers {
  static parseManifestResponseBody({ data, linked })  {
    const { application_id: appId, id, targets, name: title } = data;
    const manifest = JSON.parse(JSON.stringify(linked.app[appId].manifest));
    return { ...manifest, application_id: appId, id, targets, title };
  }

}
