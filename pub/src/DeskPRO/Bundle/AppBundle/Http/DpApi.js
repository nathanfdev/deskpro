import uuid from 'node-uuid';
import Http from 'DeskPRO/Component/Http/Http';

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
