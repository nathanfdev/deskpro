import $ from 'jquery';
import { DpApi } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/DAL/Http/UrlCorrector';
import { store } from './store';
import { widgetSessionCodeSelector } from '../Modules/Application/Selectors/bootstrap';

export const widgetApi = new DpApi($.ajax);
widgetApi.enableJsonPayloads();
widgetApi.setDefaultHeader('X-Agent-Request', 'true');
widgetApi.addInterceptor(new UrlCorrector(`${window.DP_HELPDESK_URL}portal/api/`, /^\/?DP_API\//));
widgetApi.addInterceptor({
  request: (config) => {
    const newConfig = config;
    const dpsidCode = widgetSessionCodeSelector(store.getState());

    if (dpsidCode) {
      newConfig.url = `${config.url}${(config.url.indexOf('?') !== -1 ? '&' : '?')}dpsid=${dpsidCode}`;
    }

    return newConfig;
  }
});
