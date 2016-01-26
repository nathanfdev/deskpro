export class UrlCorrector {
  constructor(baseUrl, regex = null) {
    this.baseUrl = baseUrl;
    this.regex = regex || /^\/?DP_URL\//;
  }

  request(config) {
    config.url = config.url.replace(this.regex, this.baseUrl);
    return config;
  }
}
