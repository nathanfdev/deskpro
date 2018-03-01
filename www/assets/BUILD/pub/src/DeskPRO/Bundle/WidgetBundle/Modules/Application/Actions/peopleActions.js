import Immutable from 'immutable';
import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from './bootstrapActions';
import { onlineAgentsSelector } from '../Selectors/peopleSelectors';
import { dispatchOnlineAgents } from '../../../Services/WindowApi';
import { chatFormDefaultValuesSelector, jwtTokenSelector } from '../Selectors/dpWindow';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => (dispatch, getState) => new Promise((resolve) => {
    const updateAgents = (newAgents) => {
      const state = getState();
      const oldAgents = Immutable.fromJS(Object.values(onlineAgentsSelector(state).toJS()));

      if (!oldAgents.equals(Immutable.fromJS(newAgents))) {
        dispatch(setCollection('Person', 'onlineAgents', newAgents));
        dispatchOnlineAgents();
      }

      resolve();
    };

    // try to get data from local storage
    const cachedData = lscache.get('dpWidget.onlineAgents');
    if (cachedData) {
      updateAgents(cachedData);
    }

    // background request data
    const state = getState();
    const chatDefaultValues = chatFormDefaultValuesSelector(state);
    const jwtToken = jwtTokenSelector(state);

    let defaultDepartment = chatDefaultValues && chatDefaultValues.get('department');
    if (!defaultDepartment) {
      defaultDepartment = '';
    }

    widgetApi.sendGet(`DP_API/people/online_agents?default_department=${defaultDepartment}&jwt=${jwtToken}`, { ...ajaxOptions })
      .success((response) => {
        updateAgents(response.data);
        lscache.set('dpWidget.onlineAgents', response.data, 15);
      });
  })
);
