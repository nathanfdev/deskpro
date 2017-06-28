import $ from 'jquery';
import { DpApi } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/DAL/Http/UrlCorrector';

export const widgetApi = new DpApi($.ajax);
widgetApi.enableJsonPayloads();
widgetApi.setDefaultHeader('X-Agent-Request', 'true');
widgetApi.addInterceptor(new UrlCorrector(`${window.DP_HELPDESK_URL}portal/api/`, /^\/?DP_API\//));
widgetApi.addInterceptor({
  request: (config) => {
    config.xhrFields = {
      withCredentials: true
    };

    return config;
  }
});
