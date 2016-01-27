import $ from 'jquery';
import { DpApi } from 'DeskPRO/Bundle/AppBundle/Http/DpApi';
import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';

export const widgetApi = new DpApi($.ajax);
widgetApi.enableJsonPayloads();
widgetApi.setDefaultHeader('X-Agent-Request', 'true');
widgetApi.addInterceptor(new UrlCorrector(window.DP_HELPDESK_URL + 'portal/api/', /^\/?DP_API\//));
