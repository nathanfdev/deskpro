import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { createAction } from 'Ampliflux';

export const quickSearchResetAction = createAction('APP_SEARCH_RESET');

export const quickSearchAction = createAction(
  'APP_SEARCH',
  (data) => {
    const type = encodeURIComponent(data.type.join(','));
    const query = encodeURIComponent(data.query || '');
    const limit = parseInt(data.limit) || 5;

    return api.sendGet(`DP_API/search?types=${type}&q=${query}&limit=${limit}`);
  }
);
