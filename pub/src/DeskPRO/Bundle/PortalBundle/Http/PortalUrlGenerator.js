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

    if (!ignore_lang && this.is_multi_lang) {
      return_path.push(this.lang_code);
    }

    path = path.replace(/^\/|\/$/g, ''); // remove leading/trailing slashes from input
    return_path.push(path);

    return return_path.join('/');
  }

  getSpinnerPath() {
    return this.path('/web/spinner.gif', true);
  }
}

console.log('DESKPRO_BASE_URL %s', window.DESKPRO_BASE_URL);
console.log('DESKPRO_LANG %s', window.DESKPRO_LANG);
console.log('DESKPRO_MULTI_LANG %s', window.DESKPRO_MULTI_LANG);

const url_generator = new PortalUrlGenerator(window.DESKPRO_BASE_URL, window.DESKPRO_LANG, window.DESKPRO_MULTI_LANG);

export default url_generator;
