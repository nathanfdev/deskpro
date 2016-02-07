import uuid from 'node-uuid';
import { Http } from 'DeskPRO/Component/Http/Http';
import { UrlCorrector } from './UrlCorrector';
import $ from 'jquery';

/*
class BatchRequest extends Http {
  constructor(api) {
    this.api = api;
    this.requests = [];
  }

  setDefaultHeader() {
    throw new Error("Unsupported");
  }
  addInterceptor() {
    throw new Error("Unsupported");
  }
  addResultResolver() {
    throw new Error("Unsupported");
  }
  enableJsonPayloads() {
    throw new Error("Unsupported");
  }
  setDefaultHeader() {
    throw new Error("Unsupported");
  }
  setDefaultHeader() {
    throw new Error("Unsupported");
  }
  setDefaultHeader() {
    throw new Error("Unsupported");
  }

  done() {
    return this.sendBatch(this);
  }

  send(config) {
    const batchRecord = {
      id: uuid(),
      config: config,
      promise: new Promise((resolve, reject) => {
        batchRecord._resolve = resolve;
        batchRecord._reject  = reject;
      })
    };

    this.requests.push(batchRecord);
    return batchRecord.promise;
  }
}
*/
export class DpApi extends Http {
  init() {
    this.activeBatchRequest = null;
  }

  newBatch() {
    return new BatchRequest(this);
  }

  begin() {
    if (!this.activeBatchRequest) {
      this.activeBatchRequest = this.newBatch();
    }

    return this.activeBatchRequest;
  }

  sendBatch(batch) {

  }
}

export const api = new DpApi($.ajax);
api.enableJsonPayloads();
api.setDefaultHeader('X-Agent-Request', 'true');
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

// Replace DP_API in the beginning with full URL prefix including http://
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL + '/api/v2/', /^\/?DP_API\//));

// todo note that the last interceptor overrides DP_BASE_URL of previous one
// Replace other DP_API occurrences with just '/api/v2' implying they're used to define sub-requests in batch API
api.addInterceptor(new UrlCorrector('/api/v2/', /\/?DP_API\//g));
