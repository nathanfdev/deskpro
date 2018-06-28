import { portalWindow } from '../PortalWindow';

class PortalUrlGenerator {

  /*
   * base_url must NOT contain lang_code. it is base path to index.php via the web.
   */
  constructor() {
    this.lang_code = portalWindow.lang;
    this.is_multi_lang = portalWindow.is_multi_lang;
    this.base_url = String(portalWindow.base_url).replace(/\/$/, ''); // remove trailing slash
    this.root_url = String(portalWindow.root_url).replace(/\/$/, ''); // remove trailing slash
    this.legacy_web_url = String(portalWindow.legacy_web_url).replace(/\/$/, ''); // remove trailing slash
    this.app_assets_url = String(portalWindow.app_assets_url).replace(/\/$/, ''); // remove trailing slash
  }

  path(path) {
    return PortalUrlGenerator.makeUrl(this.base_url, path);
  }

  rootPath(path) {
    return PortalUrlGenerator.makeUrl(this.root_url, path);
  }

  legacyWebPath(path) {
    return PortalUrlGenerator.makeUrl(this.legacy_web_url, path);
  }

  appAssetsPath(path) {
    return PortalUrlGenerator.makeUrl(this.app_assets_url, path);
  }

  static makeUrl(base, path) {
    const returnPath = [base];

    // remove leading/trailing slashes from path input
    const editedPath = path.replace(/^\/|\/$/g, '');
    returnPath.push(editedPath);

    return returnPath.join('/');
  }

  getSpinnerPath() {
    return this.legacyWebPath('/spinner.gif');
  }

  getFlagPath(flagImgName) {
    return this.legacyWebPath(`/images/flags/${flagImgName}`);
  }
}

export const portalUrlGenerator = new PortalUrlGenerator();
