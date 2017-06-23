export class AppAssets {

  static get iconPath() { return 'assets/icon.png'; }

  static get readmePath() { return 'README.md'; }

  constructor({ appVersion })  {
    this.props = { appVersion };
  }
  getIconUrl = baseUrl => [baseUrl, this.props.appVersion, 'files', this.iconPath].join('/');

  getReadmeUrl = baseUrl => [baseUrl, this.props.appVersion, 'files', this.readmePath].join('/');
}
