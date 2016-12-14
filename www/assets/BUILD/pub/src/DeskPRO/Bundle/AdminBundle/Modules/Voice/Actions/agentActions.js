import { createAction } from 'DeskPRO/Component/Ampliflux';
import { releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { editAgent } from '../../Application/Actions/peopleActions';

export const toggleVoiceEnabled = createAction(
  'VOICE_TOGGLE_AGENT',
  agent => (dispatch) => {
    const isVoiceEnabled = !agent.getIn(['agent_data', 'is_voice_enabled']);
    const data = {
      agent_data: {
        is_voice_enabled: isVoiceEnabled
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
