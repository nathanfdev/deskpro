import $ from "jquery";
import DpApi from "DeskPRO/Component/Http/DpApi";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";

const api = new DpApi($.ajax);
api.enableJsonPayloads();
api.setDefaultHeader('X-Agent-Request', 'true');
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL));
api.addInterceptor(new UrlCorrector(window.DP_BASE_URL + '/api/v2/', /^\/?DP_API\//));

export default api;
