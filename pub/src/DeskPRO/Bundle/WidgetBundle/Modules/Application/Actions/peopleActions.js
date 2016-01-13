import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from './bootstrapActions';
import { setPeopleRequest, releasePeopleRequest } from '../RecordStores/Actions/peopleActions';
import { onlineAgentsSelector } from '../RecordStores/Selectors/peopleSelectors';
import Immutable from 'immutable';

export const loadOnlineAgents = createAction(
  'WIDGET_LOAD_ONLINE_AGENTS',
  () => (dispatch, getState) =>
    DpApi.sendGet('DP_API/people/online_agents', {...ajaxOptions})
      .success(response => {
        const state = getState();
        const oldAgents = Immutable.fromJS(Object.values(onlineAgentsSelector(state).toJS()));

        if (!oldAgents.equals(Immutable.fromJS(response.data))) {
          dispatch(releasePeopleRequest());
          dispatch(setPeopleRequest('onlineAgents', response.data));
        }
      })
);

export const loadPeople = createAction(
  'WIDGET_LOAD_PEOPLE',
    missingIds => DpApi.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','), {...ajaxOptions})
    .success(response => response.data)
    .error(response => response)
);
