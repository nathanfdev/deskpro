import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => new Promise(resolve => {
    return DpApi
      .sendGet('DP_API/agents/online', {crossDomain: true, dataType: 'json'})
      .success(response => resolve(response.data));
  })
);
