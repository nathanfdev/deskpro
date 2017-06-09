export class AppAssets
{
  constructor({ appVersion })
  {
    this.props = { appVersion };
  }
  getIconUrl = (baseUrl) => [ baseUrl, this.props.appVersion, 'files', this.iconPath ].join('/');

  getReadmeUrl = (baseUrl) => [ baseUrl, this.props.appVersion, 'files', this.readmePath ].join('/');

  get iconPath() { return 'assets/icon.png' ; }

  get readmePath() { return 'README.md'; }
}
