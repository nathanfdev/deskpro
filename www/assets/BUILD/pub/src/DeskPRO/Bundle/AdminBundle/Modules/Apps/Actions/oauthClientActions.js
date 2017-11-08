import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadOAuthClients = createAction(
  'ADMIN_APPS_LOAD_OAUTH_CLIENTS',
  () => dispatch => dispatch(loadAll('OAuthClient'))
);

export const createOAuthClient = createAction(
  'ADMIN_APPS_CREATE_OAUTH_CLIENTS',
  data => dispatch => repository('OAuthClient').create(data).success((response) => {
    dispatch(addToCollection('OAuthClient', 'all', Immutable.List([Immutable.fromJS(response.data)])));
  })
);

export const updateOAuthClient = createAction(
  'ADMIN_APPS_UPDATE_OAUTH_CLIENTS',
  (id, data) => dispatch => repository('OAuthClient').update(data, id).success(() => {
    dispatch(updateCollection('OAuthClient', Immutable.List([Immutable.fromJS({ ...data, id })]), 'merge'));
  })
);

export const deleteOAuthClient = createAction(
  'ADMIN_APPS_DELETE_OAUTH_CLIENTS',
  id => dispatch => repository('OAuthClient').remove(id).success(() => {
    dispatch(removeFromCollection('OAuthClient', 'all', [id]));
  })
);
