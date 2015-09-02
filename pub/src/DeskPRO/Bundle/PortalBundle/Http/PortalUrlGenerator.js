class PortalUrlGenerator {
  constructor(base_url) {
    this.base_url = base_url.replace(/\/$/, '');
  }

  path(path) {
    return this.base_url + path;
  }
}

const url_generator = new PortalUrlGenerator(window.DESKPRO_BASE_URL);

export default url_generator;
