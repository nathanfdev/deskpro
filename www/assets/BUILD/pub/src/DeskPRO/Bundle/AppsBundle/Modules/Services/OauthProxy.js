import { default as URL } from 'url-parse';

/**
 *
 * @param ev
 * @param {URL} originURL
 * @return {boolean}
 */
const validateOauthProxyMessage = (ev, originURL) => {
  const { type } = ev.data;
  if (type !== 'oauth-proxy-callback') { return false; }

  const origin =  `${originURL.protocol.replace(/:+$/, '')}://${originURL.host}`;
  return origin === ev.origin;
};

function prefixDpQueryParams(params) {
  const prefix = 'dp_';
  return Object.keys(params).reduce((acc, value) => {
    acc[`${prefix}${value}`] = params[value];
    return acc;
  }, {});
}

function changeUrl(baseUrl, newParts) {
  const builder = new URL(baseUrl);
  // switch to https if we host window is using https but oauthProxyEndpoint is not, mostly affects dev environs
  const isWindowHttps = window && typeof window.location === 'object' && window.location.protocol === 'https:';
  if (isWindowHttps && builder.protocol !== 'https') {
    builder.set('protocol', 'https');
  }

  Object.keys(newParts).forEach(partName => builder.set(partName, newParts[partName]));
  return builder.toString();
}

function appendPathname(baseUrl, append) {
  const builder = new URL(baseUrl);
  let existingPath = builder.pathname;
  if (!existingPath) {
    existingPath = '';
  }
  const pathname = `${existingPath.trim('/')}/${append}`;
  return builder.set('pathname', pathname).toString();
}

export class OauthProxy {
  /**
   * @param {Base64Converter} base64
   * @param {AppsConfig} appsConfig
   * @param username
   * @param apiToken
   */
  constructor({ base64, appsConfig, username, apiToken })  {
    this.props = { base64, appsConfig, username, apiToken };
  }

  get oauthProxyEndpoint() { return this.props.appsConfig.oauthProxyEndpoint; }

  /**
   * @param {string} protocolVersion
   * @param {string} provider
   * @param others
   * @return {function|null}
   */
  buildAuthorizeUrl({ protocolVersion, provider, ...others })  {
    if (protocolVersion === '2.0' || !protocolVersion) {
      const protocolParams = {
        protocolVersion,
        provider,
        verifyUrl: (new URL(this.props.appsConfig.apiRoot)) // use canonic xxx.deskpro.com
          .set('username', this.props.username)
          .set('password', this.props.base64.encode(`token ${this.props.apiToken}`))
          .toString(),
        ...others
      };
      return this.buildOauth2AuthorizeUrl.bind(this, protocolParams);
    }

    if (['1.0', '1.0a'].indexOf(protocolVersion) !== -1) {
      return this.buildOauth1AuthorizeUrl.bind(this, { protocolVersion, provider, ...others });
    }

    throw new Error(`Unknown protocol version: ${protocolVersion}`);
  }

  buildRedirectUrl({ provider, protocolVersion, applicationId })  {
    let path = protocolVersion === '2.0' || !protocolVersion ? [] : [protocolVersion];
    path = path.concat([provider, 'grant-access', applicationId]).join('/');
    const url = appendPathname(this.oauthProxyEndpoint, path);
    return changeUrl(url, {});
  }

  buildRefreshAccessUrl({ protocolVersion, ...others }) {
    if (protocolVersion === '2.0' || !protocolVersion) {
      const protocolParams = {
        protocolVersion,
        verifyUrl: (new URL(this.props.appsConfig.apiRoot)) // use canonic xxx.deskpro.com
          .set('username', this.props.username)
          .set('password', this.props.base64.encode(`token ${this.props.apiToken}`))
          .toString(),
        ...others
      };
      return this.buildOauth2RefreshTokenUrl.bind(this, protocolParams);
    }

    throw new Error('Refresh access url is only available for oauth version 2.0');
  }

  /**
   * @param {String}    protocolVersion
   * @param {String}    provider
   * @param {Object}    [query]
   * @param {String}    callbackMethod
   * @param {String}    callbackUrl
   * @param {String}    applicationId
   * @return {String}
   */
  buildOauth1AuthorizeUrl({ protocolVersion, provider, query }, { callbackMethod, callbackUrl, applicationId }) {
    // prefix deskpro query param names
    const dpQuery = prefixDpQueryParams({ callbackMethod, callbackUrl, applicationId });
    // normalize extra query parameters
    const xQuery = query && typeof query === 'object' ? query : {};

    const url = appendPathname(this.oauthProxyEndpoint, [protocolVersion, provider, 'authorize'].join('/'));
    return changeUrl(url, { query: { ...xQuery, ...dpQuery } });
  }

  /**
   * @param protocolVersion
   * @param {String} verifyUrl
   * @param {String} provider
   * @param {Object} [query]
   * @param {String} callbackMethod
   * @param {String} callbackUrl
   * @param applicationId
   * @param correlationId
   * @return {String}
   */
  buildOauth2AuthorizeUrl({ protocolVersion, verifyUrl, provider, query }, { callbackMethod, callbackUrl, applicationId, correlationId })  {
    // prefix deskpro query param names
    const state = this.props.base64.encodeJSON({ correlationId });
    const dpQuery = prefixDpQueryParams({ verifyUrl, state, callbackMethod, callbackUrl, applicationId });
    // normalize extra query parameters
    const xQuery = query && typeof query === 'object' ? query : {};

    const url = appendPathname(this.oauthProxyEndpoint, [provider, 'authorize'].join('/'));
    return changeUrl(url, { query: { ...xQuery, ...dpQuery } });
  }

  /**
   * @param protocolVersion
   * @param {String} verifyUrl
   * @param {String} provider
   * @param {Object} [query]
   * @param {String} callbackMethod
   * @param {String} callbackUrl
   * @param applicationId
   * @param correlationId
   * @return {String}
   */
  buildOauth2RefreshTokenUrl({ protocolVersion, verifyUrl, provider, query }, { applicationId, correlationId })  {
    // prefix deskpro query param names
    const state = this.props.base64.encodeJSON({ correlationId });
    const dpQuery = prefixDpQueryParams({ verifyUrl, state, applicationId });
    // normalize extra query parameters
    const xQuery = query && typeof query === 'object' ? query : {};

    const url = appendPathname(this.oauthProxyEndpoint, [provider, 'refresh-access'].join('/'));
    return changeUrl(url, { query: { ...xQuery, ...dpQuery } });
  }

  buildReceiveTokenListener({ protocolVersion, cb, oauthProxyUrl })  {
    if (protocolVersion === '2.0' || !protocolVersion) {
      return authParams => this.onReceiveOauth2TokenListener.bind(this, { cb, oauthProxyUrl }, authParams);
    }

    if (['1.0', '1.0a'].indexOf(protocolVersion) !== -1) {
      return authParams => this.onReceiveOauth1TokenListener.bind(this, { cb, oauthProxyUrl }, authParams);
    }

    return null;
  }

  /**
   * @param {function} cb
   * @param {string} oauthProxyUrl
   * @param {string} correlationId
   * @param {{}} ev
   * @return {boolean}
   */
  onReceiveOauth1TokenListener({ cb, oauthProxyUrl }, { correlationId }, ev)  { // eslint-disable-line class-methods-use-this
    // there could two different authentication schemes running concurrently
    if (!validateOauthProxyMessage(ev, new URL(oauthProxyUrl))) {
      return false;
    }

    const { status } = ev.data;
    if (status === 'success') {
      cb(null, ev.data);
    } else {
      const { error: oauthError } = ev.data.body;
      const errorMessage = oauthError || 'authentication failed';
      cb(new Error(errorMessage), null);
    }
    return true;
  }

  /**
   * @param {function} cb
   * @param {string} oauthProxyUrl
   * @param {string} correlationId
   * @param {{}} ev
   * @return {boolean}
   */
  onReceiveOauth2TokenListener({ cb, oauthProxyUrl }, { correlationId }, ev)  {
    // there could two different authentication schemes running concurrently
    if (!validateOauthProxyMessage(ev, new URL(oauthProxyUrl))) {
      return false;
    }

    let messageIsAuthentic;
    try {
      const receivedState = this.props.base64.decodeJSON(ev.data.body.state);
      messageIsAuthentic = correlationId === receivedState.correlationId;
    } catch (error) {
      messageIsAuthentic = false;
    }

    if (!messageIsAuthentic) {
      cb(new Error('authentication failed. message appears to be tampered with'));
      return true;
    }

    const { status } = ev.data;
    if (status === 'success') {
      cb(null, ev.data);
    } else {
      const { error: oauthError } = ev.data.body;
      const errorMessage = oauthError || 'authentication failed';
      cb(new Error(errorMessage), null);
    }
    return true;
  }
}

