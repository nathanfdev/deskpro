import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { updateCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { editAgent } from '../../Application/Actions/peopleActions';

export const toggleVoiceEnabled = createAction(
  'VOICE_TOGGLE_AGENT_VOICE_ENABLED',
  agent => (dispatch) => {
    const isVoiceEnabled = !agent.getIn(['agent_data', 'is_voice_enabled']);
    const agentData = agent.get('agent_data') ? agent.get('agent_data').toJS() : {};
    const data = {
      agent_data: {
        ...agentData,
        is_voice_enabled:       isVoiceEnabled,
        outbound_calls_enabled: isVoiceEnabled
      }
    };

    const promise = dispatch(editAgent(agent.get('id'), data));
    promise.success(() => {
      if (!isVoiceEnabled) {
        // reset related data
        dispatch(releaseCollection('VoiceQueue', 'all'));
        dispatch(releaseCollection('VoiceAutoAttendant', 'all'));
      }
    });

    return promise;
  }
);

export const toggleOutboundCallsEnabled = createAction(
  'VOICE_TOGGLE_AGENT_OUTBOUND_CALL',
  agent => (dispatch) => {
    const isOutboundCallsEnabled = !agent.getIn(['agent_data', 'outbound_calls_enabled']);
    const agentData = agent.get('agent_data') ? agent.get('agent_data').toJS() : {};
    const data = {
      agent_data: {
        ...agentData,
        outbound_calls_enabled: isOutboundCallsEnabled
      }
    };

    return dispatch(editAgent(agent.get('id'), data));
  }
);

export const toggleUseForwarding = createAction(
  'VOICE_TOGGLE_AGENT_USE_FORWARDING',
  agent => (dispatch) => {
    const canUseForwarding = !agent.getIn(['agent_data', 'can_use_forwarding']);
    const agentData = agent.get('agent_data') ? agent.get('agent_data').toJS() : {};
    const data = {
      agent_data: {
        ...agentData,
        can_use_forwarding: canUseForwarding
      }
    };

    return dispatch(editAgent(agent.get('id'), data));
  }
);

export const toggleAll = createAction(
  'VOICE_AGENT_TOGGLE_ALL',
  () => (dispatch, getState) => {
    const state = getState();
    const agents = agentsSelector(state);

    // if one of the agents does not have voice enabled
    // then toggle on otherwise toggle off

    let isVoiceEnabled = false;
    agents.forEach((agent) => {
      if (!agent.getIn(['agent_data', 'is_voice_enabled'])) {
        isVoiceEnabled = true;
      }
    });

    const requests = {};
    agents.forEach((agent) => {
      // skip if already set to proper value
      if (!!agent.getIn(['agent_data', 'is_voice_enabled']) === isVoiceEnabled) {
        return;
      }

      const agentData = agent.get('agent_data') ? agent.get('agent_data').toJS() : {};

      requests[agent.get('id')] = {
        method: 'put',
        url:    `/people/${agent.get('id')}`,
        data:   {
          agent_data: {
            ...agentData,
            is_voice_enabled:       isVoiceEnabled,
            outbound_calls_enabled: isVoiceEnabled
          }
        }
      };
    });

    const promise = api.sendPost('DP_API/batch', { requests });
    promise.success(({ responses }) => {
      agents.forEach((agent) => {
        const response = responses[agent.get('id')];
        if (!response) {
          return;
        }

        const statusCode = response.headers['status-code'];
        if (statusCode === 204) {
          let newAgent = agent;
          if (!newAgent.get('agent_data')) {
            newAgent = newAgent.set('agent_data', Immutable.fromJS({}));
          }

          newAgent = newAgent.setIn(['agent_data', 'is_voice_enabled'], isVoiceEnabled);
          newAgent = newAgent.setIn(['agent_data', 'outbound_calls_enabled'], isVoiceEnabled);

          dispatch(updateCollection('Person', Immutable.List([newAgent]), 'merge'));
        }
      });
    });

    return promise;
  }
);
