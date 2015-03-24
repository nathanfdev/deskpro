export default class UrlCorrector {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
  }

  request(config) {
    config.url = config.url.replace(/^\/?DP_URL\//, this.baseUrl);
    return config;
  }
}