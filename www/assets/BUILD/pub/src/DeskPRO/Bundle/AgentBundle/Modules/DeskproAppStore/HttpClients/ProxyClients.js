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
   * @param {String} instanceId
   */
  constructor({ apiClient, instanceId }) {
    this.props = { apiClient, instanceId };
  }

  fetch = (url, init) => {
    const { headers: originalHeaders, method, ...passthroughInit } = init;
    const headers = mapHeaders(originalHeaders);

    headers['x-proxy-replacevars'] = true;
    headers['x-proxy-url'] = url;
    headers['x-proxy-method'] = method.toString().toLowerCase();
    headers['x-proxy-autoheaders'] = 'false';

    const proxyInit = { ...passthroughInit, method, mode: 'same-origin', headers };


    const { apiClient, instanceId } = this.props;
    return apiClient.fetch(`http-api-proxy/${instanceId}`, proxyInit);
  }
}
