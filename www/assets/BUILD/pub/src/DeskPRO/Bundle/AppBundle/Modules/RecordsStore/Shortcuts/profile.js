import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection, collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../index';

export const loadMyProfile = createAction(
  'PROFILE_LOAD',
  () => dispatch => api.sendGet('DP_API/me/profile')
    .success(response => dispatch(setCollection('Profile', 'my', [response.data])))
);

export const updateMyProfile = createAction(
  'PROFILE_UPDATE',
  data => dispatch => api.sendPut('DP_API/me/profile', data)
    .success(() => dispatch(loadMyProfile()))
);

export const myProfileSelector = state => collectionSelectorFactory('Profile', 'my')(state).first();

export const isMyProfileLoadedSelector = isLoadedCollectionSelectorFactory('Profile', 'my');
