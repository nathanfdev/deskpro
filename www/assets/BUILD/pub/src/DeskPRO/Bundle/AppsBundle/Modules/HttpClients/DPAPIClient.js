export class DPAPIClient {
  /**
   * @param {Http} api
   * @param {boolean} allowAbsoluteUrls
   */
  constructor({ api, allowAbsoluteUrls }) {
    this.props = { api, allowAbsoluteUrls };
  }

  fetch = (url, init) => {
    const isAbsoluteUrl = url.match(/^(?:[a-z]+:)?\/\//i);

    if (isAbsoluteUrl && !this.props.allowAbsoluteUrls) {
      throw new Error(`[API]: Invalid path: ${url}. Absolute paths are not allowed`);
    }

    const { api } = this.props;
    const { method, body, headers } = init;

    const apiEndpoint = isAbsoluteUrl ? url : ['DP_API', url].join('/');
    let requestPromise;

    switch (method.toLowerCase()) {
      case 'get':
        requestPromise = api.sendGet(apiEndpoint, { headers, jsonPayload: false });
        break;
      case 'post':
        requestPromise = api.sendPost(apiEndpoint, body, { headers, jsonPayload: false });
        break;
      case 'put':
        requestPromise = api.sendPut(apiEndpoint, body,  { headers, jsonPayload: false });
        break;
      case 'patch':
        requestPromise = api.sendPatch(apiEndpoint, body,  { headers, jsonPayload: false });
        break;
      case 'delete':
        requestPromise = api.sendDelete(apiEndpoint, { headers, jsonPayload: false });
        break;
      default:
        throw new Error(`failed to execute fetch: unknown method ${method}`);
    }

    return requestPromise;
  }


}
