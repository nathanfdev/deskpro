import $ from 'jquery';
import DpApi from 'DeskPRO/Bundle/AppBundle/Http/DpApi';
import UrlCorrector from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';

const api = new DpApi($.ajax);
api.enableJsonPayloads();
api.setDefaultHeader('X-Agent-Request', 'true');
api.addInterceptor(new UrlCorrector(window.DP_HELPDESK_URL + 'portal/api/', /^\/?DP_API\//));

export default api;
