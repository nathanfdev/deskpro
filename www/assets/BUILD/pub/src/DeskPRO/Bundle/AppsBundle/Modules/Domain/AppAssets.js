export class AppAssets {

  constructor({ appVersion })  {
    this.props = { appVersion, iconPath: 'assets/icon.png', readmePath: 'README.md' };
  }
  getIconUrl = baseUrl => [baseUrl, this.props.appVersion, 'files', this.iconPath].join('/');

  getReadmeUrl = baseUrl => [baseUrl, this.props.appVersion, 'files', this.readmePath].join('/');

  get iconPath() { return this.props.iconPath; }

  get readmePath() { return this.props.readmePath; }
}
