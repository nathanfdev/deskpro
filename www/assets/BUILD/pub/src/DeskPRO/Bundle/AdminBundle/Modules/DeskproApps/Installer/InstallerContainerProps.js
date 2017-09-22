export class InstallerContainerProps {
  /**
   * @param {string} [app]
   * @param {function} [loadAppManifest]
   */
  constructor({ app, loadAppManifest })  {
    this.state = { app, loadAppManifest };
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
   * @param {{}} params
   * @return {InstallerContainerProps}
   */
  setRouteProps({ params })  {
    if (!params) {
      return this;
    }

    const { app } = params;
    this.state =  { ...this.state, app };
    return this;
  }

  toJS() { return { ...this.state }; }


}

