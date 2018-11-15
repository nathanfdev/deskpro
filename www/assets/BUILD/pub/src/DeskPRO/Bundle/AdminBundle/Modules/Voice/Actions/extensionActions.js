import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import { updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { editAgent } from '../../Application/Actions/peopleActions';
import { allAgentsSelector } from '../../Application/Selectors/people';

export const addExtension = createAction(
  'VOICE_EDIT_EXTENSION',
  (agentId, extensionNumber) => (dispatch) => {
    const data = {
      agent_data: {
        extension_number: extensionNumber
      }
    };

    return dispatch(editAgent(agentId, data));
  }
);

export const addExtensions = createAction(
  'VOICE_ADD_EXTENSIONS',
  extensionNumbers => (dispatch, getState) => {
    const requests = {};
    extensionNumbers.forEach(({ agentId, extensionNumber }) => {
      requests[agentId] = {
        method: 'put',
        url:    `/people/${agentId}`,
        data:   {
          agent_data: {
            extension_number: extensionNumber
          }
        }
      };
    });

    return api.sendPost('DP_API/batch', { requests }).success(({ responses }) => {
      extensionNumbers.forEach(({ agentId, extensionNumber }) => {
        const response = responses[agentId];
        const statusCode = response.headers['status-code'];

        if (statusCode === 204) {
          const state = getState();
          const agents = allAgentsSelector(state);

          let agent = agents.get(parseInt(agentId, 10));
          if (agent) {
            if (!agent.get('agent_data')) {
              agent = agent.set('agent_data', Immutable.fromJS({
                extension_number: null
              }));
            }

            agent = agent.setIn(['agent_data', 'extension_number'], extensionNumber);
            dispatch(updateCollection('Person', Immutable.List([agent]), 'merge'));
          }
        }
      });
    });
  }
);
