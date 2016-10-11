import { createAction } from 'DeskPRO/Component/Ampliflux';

export const clickLogo = createAction(
  'TOPBAR_CLICK_LOGO',
  (onboardingId, params) => params
);

