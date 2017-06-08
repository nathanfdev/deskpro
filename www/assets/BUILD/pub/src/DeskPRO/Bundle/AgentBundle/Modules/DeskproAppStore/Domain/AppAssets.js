export class AppAssets
{
  getIconUrl = (url) => [ url, this.iconPath ].join('/');

  getReadmeUrl = (url) => [ url, this.readmePath ].join('/');

  get iconPath() { return 'assets/icon.png' ; }

  get readmePath() { return 'README.md'; }
}
