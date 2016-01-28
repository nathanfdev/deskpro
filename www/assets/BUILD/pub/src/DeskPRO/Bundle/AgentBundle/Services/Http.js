import $ from 'jquery';
import Http from 'DeskPRO/Component/Http/Http';
import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';

const http = new Http($.ajax);
http.enableJsonPayloads();
http.addInterceptor(new UrlCorrector(window.DP_BASE_URL));
http.addInterceptor(new UrlCorrector(window.DP_BASE_URL + '/api/v2', /^\/?DP_API\//));

export default http;
