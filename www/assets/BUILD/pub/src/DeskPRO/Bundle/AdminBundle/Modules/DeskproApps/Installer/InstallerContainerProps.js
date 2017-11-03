export class InstallerContainerProps {
  /**
   * @param {string} [app]
   * @param {function} [loadAppManifest]
   * @param {function} [loadInstallerManifest]
   */
  constructor({ app, loadAppManifest, loadInstallerManifest })  {
    this.state = { app, loadAppManifest, loadInstallerManifest };
  }

  /**
   * @type {string}
   */
  get app()  {
    return this.state.app;
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
   * @param {{}} params
   * @return {InstallerContainerProps}
   */
  setRouteProps({ params })  {
    if (!params || typeof params !== 'object') {
      return this;
    }

    const { app } = params;
    this.state =  { ...this.state, app: decodeURIComponent(app) };
    return this;
  }

  toJS() { return { ...this.state }; }

}

