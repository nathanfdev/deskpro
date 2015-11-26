import $ from 'jquery';
import DpApi from 'DeskPRO/Bundle/AppBundle/Http/DpApi';
import UrlCorrector from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';

const api = new DpApi($.ajax);
api.enableJsonPayloads();
api.setDefaultHeader('X-Agent-Request', 'true');
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

// Replace DP_API in the beginning with full URL prefix including http://
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL + '/portal/api/', /^\/?DP_API\//));

// Replace other DP_API occurrences with just '/api/v2' implying they're used to define sub-requests in batch API
api.addInterceptor(new UrlCorrector('/api/v2/', /\/?DP_API\//g));

export default api;
