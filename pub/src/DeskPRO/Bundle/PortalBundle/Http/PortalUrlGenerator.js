import PortalWindow from 'DeskPRO/Bundle/PortalBundle/PortalWindow';

class PortalUrlGenerator {
  /**
   * base_url must NOT contain lang_code. it is base path to index.php via the web.
   */
  constructor(base_url, lang_code, is_multi_lang) {
    this.lang_code = lang_code;
    this.is_multi_lang = is_multi_lang;
    this.base_url = base_url.replace(/\/$/, ''); // remove trailing slash from base url
  }

  path(path, ignore_lang = false) {
    let return_path = [this.base_url];

    // push the current lang_code into the path if it is a mult-lang site
    if (!ignore_lang && this.is_multi_lang) {
      return_path.push(this.lang_code);
    }

    // remove leading/trailing slashes from path input
    path = path.replace(/^\/|\/$/g, '');
    return_path.push(path);

    return return_path.join('/');
  }

  getSpinnerPath() {
    return this.path('/web/spinner.gif', true);
  }

  getFlagPath(flag_img_name) {
    return this.path(`/web/images/flags/${flag_img_name}`, true);
  }
}

const url_generator = new PortalUrlGenerator(PortalWindow.base_url, PortalWindow.lang, PortalWindow.is_multi_lang);

export default url_generator;
