import { Http } from 'DeskPRO/Component/Http/Http';
import $ from 'jquery';
import { UrlCorrector } from './UrlCorrector';

export class DpApi extends Http {
  prepareParams(batchComponents) { // eslint-disable-line class-methods-use-this
    const batchParams = [];

    Object.keys(batchComponents).forEach(
      (key) => {
        let str = `get[${key}]=DP_API_SUB/${batchComponents[key].endpoint}`;
        if (batchComponents[key].query) {
          str += `?${encodeURIComponent(batchComponents[key].query)}`;
        }
        batchParams.push(str);
      }
    );
    return `DP_API/batch?${batchParams.join('&')}`;
  }
}

export const api = new DpApi($.ajax);
api.enableJsonPayloads();
api.setDefaultHeader('X-Agent-Request', 'true');
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

// Replace DP_API in the beginning with full URL prefix including http://
api.addInterceptor(new UrlCorrector(`${window.DP_BASE_API_URL}/v2/`, /^\/?DP_API\//));
api.addInterceptor(new UrlCorrector(`${window.DP_BASE_API_URL}/`, /^\/?DP_API_OLD\//));

// todo note that the last interceptor overrides DP_BASE_URL of previous one
// Replace other DP_API occurrences with just '/api/v2' implying they're used to define sub-requests in batch API
api.addInterceptor(new UrlCorrector('/api/v2/', /\/?DP_API_SUB\//g));
