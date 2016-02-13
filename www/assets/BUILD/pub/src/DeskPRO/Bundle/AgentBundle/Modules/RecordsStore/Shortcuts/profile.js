import { createSelector } from 'reselect';
import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../index';
import { setCollection } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

export const loadMyProfile = createAction(
  'PROFILE_LOAD',
  () => dispatch => api.sendGet('DP_API/me/profile')
                       .success(response => dispatch(setCollection('Profile', 'my', [response.data])))
);

export const updateMyProfile = createAction(
  'PROFILE_UPDATE',
  data => dispatch => api.sendPut('DP_API/me/profile', data)
                         .success(response => dispatch(setCollection('Profile', 'my', [response.data])))
);

export const myProfileSelector = function(state) {
  return collectionSelectorFactory('Profile', 'my')(state).first()
};

export const isMyProfileLoadedSelector = isLoadedCollectionSelectorFactory('Profile', 'my');