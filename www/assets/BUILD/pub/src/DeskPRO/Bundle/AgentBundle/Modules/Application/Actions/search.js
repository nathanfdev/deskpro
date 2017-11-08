import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { createAction } from 'Ampliflux';

export const quickSearchAction = createAction(
  'APP_SEARCH',
  (data) => {
    const types = encodeURIComponent(data.types.join(','));
    const query = encodeURIComponent(data.query || '');
    const limit = parseInt(data.limit, 10) || 5;

    return api.sendGet(`DP_API/search?types=${types}&q=${query}&limit=${limit}`);
  }
);
