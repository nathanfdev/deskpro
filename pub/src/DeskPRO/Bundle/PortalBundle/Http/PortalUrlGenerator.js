import PortalWindow from 'DeskPRO/Bundle/PortalBundle/PortalWindow';

class PortalUrlGenerator {

  /*
   * base_url must NOT contain lang_code. it is base path to index.php via the web.
   */
  constructor(portalWindow) {
    this.lang_code = portalWindow.lang;
    this.is_multi_lang = portalWindow.is_multi_lang;
    this.base_url = String(portalWindow.base_url).replace(/\/$/, ''); // remove trailing slash
    this.root_url = String(portalWindow.root_url).replace(/\/$/, ''); // remove trailing slash
    this.web_url = String(portalWindow.web_url).replace(/\/$/, ''); // remove trailing slash
  }

  path(path) {
    return this._makeUrl(this.base_url, path);
  }

  rootPath(path) {
    return this._makeUrl(this.root_url, path);
  }

  webPath(path) {
    return this._makeUrl(this.web_url, path);
  }

  _makeUrl(base, path) {
    const returnPath = [base];

    // remove leading/trailing slashes from path input
    const editedPath = path.replace(/^\/|\/$/g, '');
    returnPath.push(editedPath);

    return returnPath.join('/');
  }

  getSpinnerPath() {
    return this.webPath('/spinner.gif');
  }

  getFlagPath(flagImgName) {
    return this.webPath(`/images/flags/${flagImgName}`);
  }
}

const urlGenerator = new PortalUrlGenerator(PortalWindow);

export default urlGenerator;
