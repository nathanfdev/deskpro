class DeskproAppStoreConfiguration
{
  static get validTargets()
  {
    return ['top-bar', 'ticket-sidebar'];
  }

  static get validEnvironments()
  {
    return ['development', 'production'];
  }

  static get devAppManifestUrl()
  {
    return 'http://127.0.0.1:31080/manifest.json';
  }

  constructor (environment, location)
  {
    this.state = { environment, location};
  }

  get environment()
  {
    return this.state.environment;
  }

  get endpoint()
  {
    if (this.environment === 'development') {
      return 'http://127.0.0.1:31080';
    }

    const { location } = this.state;
    return location.origin;
  }
}

export default DeskproAppStoreConfiguration;
