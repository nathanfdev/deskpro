import { createAction } from 'Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from './bootstrapActions';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { onlineAgentsSelector } from '../Selectors/peopleSelectors';
import * as windowApiActions from '../../../Services/WindowApi';
import Immutable from 'immutable';
import lscache from 'lscache';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const oldAgents = Immutable.fromJS(Object.values(onlineAgentsSelector(state).toJS()));

    const updateAgents = (newAgents) => {
      if (!oldAgents.equals(Immutable.fromJS(newAgents))) {
        dispatch(setCollection('Person', 'onlineAgents', newAgents));
        windowApiActions.getOnlineAgents();
      }

      resolve();
    };

    // try to get data from local storage
    const cachedData = lscache.get('dpWidget.onlineAgents');
    if (cachedData) {
      updateAgents(cachedData);
    }

    // background request data
    widgetApi.sendGet('DP_API/people/online_agents', { ...ajaxOptions })
      .success(response => {
        updateAgents(response.data);
        lscache.set('dpWidget.onlineAgents', response.data, 15);
      });
  })
);
