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
   * @param protocolVersion
   * @return {function|null}
   */
  buildAuthorizeUrl({ protocolVersion })  {
    if (protocolVersion === '2.0' || !protocolVersion) {
      const protocolParams = {
        protocolVersion,
        verifyUrl: this.buildURL(this.props.appsConfig.apiRoot) // use canonic xxx.deskpro.com
          .set('username', this.props.username)
          .set('password', this.props.base64.encode(`token ${this.props.apiToken}`))
          .toString()
      };
      return this.buildOauth2AuthorizeUrl.bind(this, protocolParams);
    }

    if (['1.0', '1.0a'].indexOf(protocolVersion) !== -1) {
      return this.buildOauth1AuthorizeUrl.bind(this, { protocolVersion });
    }

    return null;
  }

  /**
   * @param protocolVersion
   * @param provider
   * @param callbackMethod
   * @param callbackUrl
   * @param applicationId
   * @return {String}
   */
  buildOauth1AuthorizeUrl({ protocolVersion }, { provider, callbackMethod, callbackUrl, applicationId }) {
    const builder = new URL(this.oauthProxyEndpoint, true);

    let existingPath = builder.pathname;
    if (!existingPath) {
      existingPath = '';
    }
    existingPath = [existingPath.trim('/'), protocolVersion].join('/');

    const pathname = `${existingPath}/${provider}/authorize`;
    return builder
        .set('protocol', 'https').set('pathname', pathname)
        .set('query', { callbackMethod, callbackUrl, applicationId })
        .toString()
    ;
  }

  /**
   * @param protocolVersion
   * @param {String} verifyUrl
   * @param {String} provider
   * @param {String} callbackMethod
   * @param {String} callbackUrl
   * @param applicationId
   * @param correlationId
   * @return {String}
   */
  buildOauth2AuthorizeUrl({ protocolVersion, verifyUrl }, { provider, callbackMethod, callbackUrl, applicationId, correlationId })  {
    const builder = new URL(this.oauthProxyEndpoint, true);

    const state = this.props.base64.encodeJSON({ correlationId });

    let existingPath = builder.pathname;
    if (!existingPath) {
      existingPath = '';
    }
    const pathname = `${existingPath.trim('/')}/${provider}/authorize`;

    return builder.set('protocol', 'https')
      .set('pathname', pathname)
      .set('query', { verifyUrl, state, callbackMethod, callbackUrl, applicationId })
      .toString()
      ;
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
  onReceiveOauth1TokenListener({ cb, oauthProxyUrl }, { correlationId }, ev)  {
    // there could two different authentication schemes running concurrently
    if (!validateOauthProxyMessage(ev, this.buildURL(oauthProxyUrl))) {
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
    if (!validateOauthProxyMessage(ev, this.buildURL(oauthProxyUrl))) {
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
      cb(new Error('authentication failed'));
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

  /**
   * @param urlString
   * @return {URL}
   */
  buildURL(urlString) {
    // es-lint forces the use of this in class methods....
    const { props } = this;
    const parseUrlString = !!props || true;

    return new URL(urlString, parseUrlString);
  }

  buildRedirectUrl({ provider, protocolVersion, applicationId })  {
    const builder = new URL(this.oauthProxyEndpoint, true);

    let existingPath = builder.pathname;
    if (!existingPath) {
      existingPath = '';
    }

    let pathPrefix = existingPath.trim('/');
    pathPrefix = protocolVersion === '2.0' || !protocolVersion ? pathPrefix : [pathPrefix, protocolVersion].join('/');

    const pathname = `${pathPrefix}/${provider}/grant-access/${applicationId}`;
    return builder.set('protocol', 'https').set('pathname', pathname);
  }
}

