import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from './bootstrapActions';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => new Promise(resolve => {
    const promise = DpApi.sendGet('DP_API/agents/online', {...ajaxOptions});
    promise.success(response => resolve(response.data));

    return promise;
  })
);
