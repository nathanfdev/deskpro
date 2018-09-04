import { default as URL } from 'url-parse';
import { encodeJSON, decodeJSON } from './base64';
import { changeUrl, appendPathname } from './url';

/**
 *
 * @param ev
 * @param {URL} originURL
 * @return {boolean}
 */
function validateOauthProxyMessage(ev, originURL) {
  const { type } = ev.data;
  if (type !== 'oauth-proxy-callback') { return false; }

  const origin =  `${originURL.protocol.replace(/:+$/, '')}://${originURL.host}`;
  return origin === ev.origin;
}

function prefixDpQueryParams(params) {
  const prefix = 'dp_';
  return Object.keys(params).reduce((acc, value) => {
    acc[`${prefix}${value}`] = params[value];
    return acc;
  }, {});
}

/**
 * @param {OauthProxy} oauthProxy
 * @param provider
 * @param protocolVersion
 * @param grant
 * @param applicationId
 * @return {*}
 */
function buildRedirectUrl(oauthProxy, { provider, protocolVersion, grant }, { applicationId })  {
  let path = protocolVersion === '2.0' || !protocolVersion ? [] : [protocolVersion];

  if (protocolVersion === '2.0' && grant === 'implicit') {
    path = path.concat([provider, 'grant-access-implicit', applicationId]).join('/');
  } else {
    path = path.concat([provider, 'grant-access', applicationId]).join('/');
  }

  const url = appendPathname(oauthProxy.oauthProxyEndpoint, path);
  return changeUrl(url, {});
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
function buildOauth1AuthorizeUrl({ protocolVersion, provider, query }, { callbackMethod, callbackUrl, applicationId }) {
  // prefix deskpro query param names
  const dpQuery = prefixDpQueryParams({ callbackMethod, callbackUrl, applicationId });
  // normalize extra query parameters
  const customQuery = query && typeof query === 'object' ? query : {};

  const url = appendPathname(this.oauthProxyEndpoint, [protocolVersion, provider, 'authorize'].join('/'));
  return changeUrl(url, { query: { ...customQuery, ...dpQuery } });
}

/**
 * @param protocolVersion
 * @param {String} provider
 * @param {Object} [query]
 * @param {String} callbackMethod
 * @param {String} callbackUrl
 * @param applicationId
 * @param correlationId
 * @return {String}
 */
function buildOauth2AuthorizeUrl({ protocolVersion, provider, query }, { callbackMethod, callbackUrl, applicationId, correlationId })  {
  // prefix deskpro query param names
  const state = encodeJSON({ correlationId });
  const dpQuery = prefixDpQueryParams({ state, callbackMethod, callbackUrl, applicationId });
  // normalize extra query parameters
  const customQuery = query && typeof query === 'object' ? query : {};

  const url = appendPathname(this.oauthProxyEndpoint, [provider, 'authorize'].join('/'));
  return changeUrl(url, { query: { ...customQuery, ...dpQuery } });
}

function buildOauth2AuthorizeImplicitUrl(oauthProxy, { authorizeUri, clientId, query, ...otherProtocolParams }, widgetParams)  {
  const dpQuery = {
    response_type: 'token',
    client_id:     clientId,
    redirect_uri:  buildRedirectUrl(oauthProxy, { authorizeUri, clientId, query, ...otherProtocolParams }, widgetParams)
  };

  const customQuery = query && typeof query === 'object' ? query : {};
  return changeUrl(authorizeUri, { query: { ...dpQuery, ...customQuery } });
}

/**
 * @param {OauthProxy} oauthProxy
 * @param protocolVersion
 * @param {String} provider
 * @param {Object} [query]
 * @param {String} callbackMethod
 * @param {String} callbackUrl
 * @param applicationId
 * @param correlationId
 * @return {String}
 */
function buildOauth2RefreshTokenUrl(oauthProxy, { protocolVersion, provider, query }, { applicationId, correlationId })  {
  // prefix deskpro query param names
  const state = encodeJSON({ correlationId });
  const dpQuery = prefixDpQueryParams({ state, applicationId });
  // normalize extra query parameters
  const customQuery = query && typeof query === 'object' ? query : {};

  const url = appendPathname(oauthProxy.oauthProxyEndpoint, [provider, 'refresh-access'].join('/'));
  return changeUrl(url, { query: { ...customQuery, ...dpQuery } });
}


/**
 * @param {function} cb
 * @param {string} oauthProxyUrl
 * @param {string} correlationId
 * @param {{}} ev
 * @return {boolean}
 */
function onReceiveMessage({ cb, oauthProxyUrl }, ev)  { // eslint-disable-line class-methods-use-this
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
function onReceiveMessageOauth2Implicit({ cb, oauthProxyUrl }, ev)  { // eslint-disable-line class-methods-use-this
  // there could two different authentication schemes running concurrently
  if (!validateOauthProxyMessage(ev, new URL(oauthProxyUrl))) {
    return false;
  }

  const { status } = ev.data;
  if (status === 'success') {
    const { hash } = ev.data.body;
    const body = {
      token: URL.qs.parse(hash.substring(1))
    };

    cb(null, { oauthVersion: '2.0',  body });
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
function onReceiveOauth2TokenListener({ cb, oauthProxyUrl, correlationId }, ev)  {
  // there could two different authentication schemes running concurrently
  if (!validateOauthProxyMessage(ev, new URL(oauthProxyUrl))) {
    return false;
  }

  let messageIsAuthentic;
  try {
    const receivedState = decodeJSON(ev.data.body.state);
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


export class OauthProxy {
  /**
   * @param {AppsConfig} appsConfig
   * @param username
   * @param apiToken
   */
  constructor({ appsConfig, username, apiToken })  {
    this.props = { appsConfig, username, apiToken };
  }

  get oauthProxyEndpoint() { return this.props.appsConfig.oauthProxyEndpoint; }

  /**
   * @param {Object} protocolParams
   * @param {Object} widgetParams
   * @return {{urlRedirect: *}}
   */
  getWidgetSettings(protocolParams, widgetParams)  {
    return {
      urlRedirect: buildRedirectUrl(this, protocolParams, widgetParams)
    };
  }

  /**
   * @param {string} protocolVersion
   * @param grant
   * @param others
   * @return {function|null}
   */
  buildAuthorizeUrl({ protocolVersion, grant, ...others })  {
    if (protocolVersion === '2.0' && grant === 'implicit') {
      const protocolParams = { protocolVersion, grant, ...others };
      return buildOauth2AuthorizeImplicitUrl.bind(null, this, protocolParams);
    }

    if (protocolVersion === '2.0' || !protocolVersion) {
      const protocolParams = { protocolVersion, grant, ...others };
      return buildOauth2AuthorizeUrl.bind(this, protocolParams);
    }

    if (['1.0', '1.0a'].indexOf(protocolVersion) !== -1) {
      return buildOauth1AuthorizeUrl.bind(null, this, { protocolVersion, grant, ...others });
    }

    throw new Error(`Failed to build oauth2 authorize url: ${protocolVersion}`);
  }

  buildRefreshAccessUrl({ protocolVersion, ...others }) {
    if (protocolVersion === '2.0' || !protocolVersion) {
      const protocolParams = { protocolVersion, ...others };
      return buildOauth2RefreshTokenUrl.bind(null, this, protocolParams);
    }

    throw new Error('Refresh access url is only available for oauth version 2.0');
  }

  buildReceiveTokenListener({ protocolVersion, grant })  {
    const { oauthProxyEndpoint : oauthProxyUrl } = this;
    if (protocolVersion === '2.0' && grant === 'implicit') {
      return ({ cb }) => onReceiveMessageOauth2Implicit.bind(null, { cb, oauthProxyUrl });
    }

    if (protocolVersion === '2.0' || !protocolVersion) {
      return ({ cb, correlationId }) => onReceiveOauth2TokenListener.bind(null, { cb, oauthProxyUrl, correlationId });
    }

    if (['1.0', '1.0a'].indexOf(protocolVersion) !== -1) {
      return ({ cb }) => onReceiveMessage.bind(null, { cb, oauthProxyUrl });
    }

    return null;
  }
}

