import { createAction } from 'DeskPRO/Component/Ampliflux';
import { releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
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
