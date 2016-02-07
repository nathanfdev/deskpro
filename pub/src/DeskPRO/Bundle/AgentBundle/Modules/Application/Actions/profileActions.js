import { createAction } from 'Ampliflux';
import * as ProfilesActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/profilesActions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export const updateProfile = createAction(
  'PROFILE_UPDATE',
  data => dispatch =>
    api.sendPut('DP_API/me/profile', data)
      .success(response => {
        const records = {};
        records[response.data.id] = response.data;

        dispatch(ProfilesActions.releaseProfiles('my'));
        dispatch(ProfilesActions.setProfilesRequest('my', records, [response.data.id]));
      })
);
