import PortalWindow from 'DeskPRO/Bundle/PortalBundle/PortalWindow';

class PortalUrlGenerator {
  /**
   * base_url must NOT contain lang_code. it is base path to index.php via the web.
   */
  constructor(portal_window) {
    this.lang_code = portal_window.lang;
    this.is_multi_lang = portal_window.is_multi_lang;
    this.base_url = portal_window.base_url.replace(/\/$/, ''); // remove trailing slash
    this.root_url = portal_window.root_url.replace(/\/$/, ''); // remove trailing slash
    this.web_url = portal_window.web_url.replace(/\/$/, ''); // remove trailing slash
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
    let return_path = [base];

    // remove leading/trailing slashes from path input
    path = path.replace(/^\/|\/$/g, '');
    return_path.push(path);

    return return_path.join('/');
  }

  getSpinnerPath() {
    return this.webPath('/spinner.gif');
  }

  getFlagPath(flag_img_name) {
    return this.webPath(`/images/flags/${flag_img_name}`);
  }
}

const url_generator = new PortalUrlGenerator(PortalWindow);

export default url_generator;
