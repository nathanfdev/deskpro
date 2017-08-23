const mapHeaders = (headers) => {
  // let's assume init.headers is an object literal
  const reducer = (mappedHeaders, header) => {
    mappedHeaders[`x-proxy-header-${header}`] = headers[header];
    return mappedHeaders;
  };

  return Object.keys(headers).reduce(reducer, {});
};

export class InstanceProxyClient {
  /**
   * @param {DPAPIClient} apiClient
   * @param {String} httpProxyEndpoint
   * @param {String} instanceId
   */
  constructor({ apiClient, httpProxyEndpoint, instanceId }) {
    this.props = { apiClient, httpProxyEndpoint, instanceId };
  }

  fetch = (url, init) => {
    const { headers: originalHeaders, method, ...passthroughInit } = init;
    const headers = mapHeaders(originalHeaders);

    headers['x-proxy-replacevars'] = true;
    headers['x-proxy-url'] = url;
    headers['x-proxy-method'] = method.toString().toLowerCase();
    headers['x-proxy-autoheaders'] = 'false';

    const proxyInit = { ...passthroughInit, method, mode: 'same-origin', headers };


    const { apiClient, httpProxyEndpoint, instanceId } = this.props;
    return apiClient.fetch(`${httpProxyEndpoint}/${instanceId}`, proxyInit);
  }
}
