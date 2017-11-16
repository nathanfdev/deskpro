export class InstallerContainerProps {
  /**
   * @param {string} [app]
   * @param {function} [loadAppManifest]
   * @param {function} [loadInstallerManifest]
   * @param {function} [loadPackageManifest]
   */
  constructor({ app, loadAppManifest, loadInstallerManifest, loadPackageManifest })  {
    this.state = { app, loadAppManifest, loadInstallerManifest, loadPackageManifest };
  }

  /**
   * @type {string}
   */
  get app()  {
    return this.state.app;
  }

  /**
   * @type {string}
   */
  get installAction()  {
    return this.state.installAction;
  }

  /**
   * @type {function}
   */
  get loadAppManifest()  {
    return this.state.loadAppManifest;
  }

  /**
   * @type {function}
   */
  set loadAppManifest(loader)  {
    this.state.loadAppManifest = loader;
  }

  /**
   * @type {function}
   */
  get loadInstallerManifest()  {
    return this.state.loadInstallerManifest;
  }

  /**
   * @type {function}
   */
  set loadInstallerManifest(loader)  {
    this.state.loadInstallerManifest = loader;
  }

  /**
   * @type {function}
   */
  get loadPackageManifest()  {
    return this.state.loadPackageManifest;
  }

  /**
   * @type {function}
   */
  set loadPackageManifest(loader)  {
    this.state.loadPackageManifest = loader;
  }

  /**
   * @param {{}} params
   * @return {InstallerContainerProps}
   */
  setRouteProps({ params })  {
    if (!params || typeof params !== 'object') {
      return this;
    }

    const { app, action } = params;
    this.state =  { ...this.state, app: decodeURIComponent(app), installAction: action };
    return this;
  }

  toJS() { return { ...this.state }; }

}

