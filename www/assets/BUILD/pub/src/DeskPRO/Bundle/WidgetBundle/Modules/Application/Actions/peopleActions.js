import { createAction } from 'Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from './bootstrapActions';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { onlineAgentsSelector } from '../Selectors/peopleSelectors';
import Immutable from 'immutable';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => (dispatch, getState) =>
    widgetApi.sendGet('DP_API/people/online_agents', {...ajaxOptions})
      .success(response => {
        const state = getState();
        const oldAgents = Immutable.fromJS(Object.values(onlineAgentsSelector(state).toJS()));

        if (!oldAgents.equals(Immutable.fromJS(response.data))) {
          dispatch(setCollection('Person', 'onlineAgents', response.data));
        }
      })
);
