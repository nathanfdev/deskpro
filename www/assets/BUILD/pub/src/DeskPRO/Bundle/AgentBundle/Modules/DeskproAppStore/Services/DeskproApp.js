
/**
 * Merges two target hashes
 *
 * @param {Object} to
 * @param {Object} from
 * @return {Object}
 */
function mergeTargets(to, from) {
  Object
    .keys(from)
    .forEach(key => to[key] = to.hasOwnProperty(key) ? to[key].concat(from[key]) : [].concat(from[key]));

  return to;
}

class DeskproApp
{
  /**
   * @param {Array<Object>} appList
   */
  static instancesFromApplicationJSList(appList) {

    return appList
      .filter(() => true) // TOOD : proper filter to validate manifests
      .map(app => this.instancesFromApplicationJS(app))
      .reduce((hash, target) => mergeTargets(hash, target), {})
    ;
  }

  /**
   * @param {Object} app
   */
  static instancesFromApplicationJS(app) {

    const { manifest, id } = app;
    const { targets } = manifest;
    return targets
      .map(targetDef => this.createTarget(id, targetDef))
      .reduce((hash, target) => mergeTargets(hash, target), {})
    ;
  }

  /**
   * @param {String} appId
   * @param {Object} targetDef
   */
  static createTarget(appId, targetDef)
  {
    const { target } = targetDef;
    //const tag = `${target}-${appId}`;
    //TODO the tag set here needs to match the tag set in the iframe by the SDK otherwise communication will not happen
    const tag = target;
    const url = `http://127.0.0.1:31080/${targetDef.url}`;

    const xcomponent = {
      tag,
      url,
      dimensions: { width: 600, height: 200 },
      timeout: 1000, // seconds
      // The properties they can (or must) pass down to my component
      props: {
        onDpMessage: {
          type: 'function',
          required: true
        }
      }
    };

    return { [target] : [ xcomponent ] }
  }
}

export default DeskproApp;
