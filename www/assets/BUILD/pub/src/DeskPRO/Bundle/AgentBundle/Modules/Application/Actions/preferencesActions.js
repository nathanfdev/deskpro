import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadQrCode = createAction(
  'APP_PREFERENES_LOAD_QR',
  () => new Promise(resolve =>
    api.sendGet('DP_API/me/devise-setup-token').success(response => resolve(response.data.setup_token)))
);
