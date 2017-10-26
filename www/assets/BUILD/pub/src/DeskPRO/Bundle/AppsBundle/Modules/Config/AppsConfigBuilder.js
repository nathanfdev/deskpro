import { AppsConfig } from './AppsConfig';

export class AppsConfigBuilder {
  constructor()  {
    this.state = {
      windowProps:    {},
      discoveryProps: {}
    };
  }

  /**
   * @param {Window} windowObject
   * @return {AppsConfigBuilder}
   */
  addWindowParams(windowObject)  {
    const { location:locationObject }  = windowObject;

    const { search } = locationObject;
    const environment = 'production';
    const configParamPrefix = 'appstore.';

    const windowProps = search.substring(1).split('&')
        .map(nameAndValue => nameAndValue.split('='))
        .filter((nameAndValue) => {
          const [name] = nameAndValue;
          return name.substr(0, configParamPrefix.length) === configParamPrefix;
        })
        .reduce((acc, nameAndValue) => {
          const [name, value] = nameAndValue;
          const key = name.substr(configParamPrefix.length);
          acc[key] = value;
          return acc;
        }, {})
      ;

    if (!windowProps.environment || AppsConfig.validEnvironments.indexOf(windowProps.environment) === -1) {
      windowProps.environment = environment;
    }

    const isDev = windowProps.environment === 'development';
    windowProps.endpoint = isDev ? AppsConfig.devEndpoint : locationObject.origin;

    const defaultApiRoot = `${locationObject.protocol}//${locationObject.host}${windowObject.DP_BASE_URL}`;
    windowProps.apiRoot = isDev && windowProps.apiRoot ? windowProps.apiRoot : defaultApiRoot;

    if (windowObject.DP_API_TOKEN) {
      windowProps.apiToken = windowObject.DP_API_TOKEN;
    }

    this.state.windowProps = windowProps;
    return this;
  }

  /**
   * @param {{}} settings
   * @return {AppsConfigBuilder}
   */
  addHelpdeskDiscoverySettings(settings)  {
    this.state.discoveryProps = {
      oauthProxyEndpoint: `${settings.apps_oauth_proxy_url}`,
      httpProxyEndpoint:  `${settings.apps_http_proxy_url}`,
      helpdeskUrl:        `${settings.helpdesk_url}`,
      helpdeskBuild:      `${settings.build}`,
      apiEndpoint:        `${settings.base_api_url}`
    };

    return this;
  }

  /**
   * @return {AppsConfig}
   */
  build()  {
    const { windowProps, discoveryProps } = this.state;
    if (windowProps.environment === 'development') { // window props override discovery props
      const props = Object.assign({}, discoveryProps, windowProps);
      return new AppsConfig(props);
    }

    const props = Object.assign({}, windowProps, discoveryProps);
    return new AppsConfig(props);
  }
}
