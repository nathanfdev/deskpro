import { createAction } from 'DeskPRO/Component/Ampliflux';

export const updateCurrentStep = createAction(
  'ONBOARDING_UPDATE_CURRENT_STEP',
  (onboardingId, params) => {
    console.log(onboardingId);
    console.log(params);
  }
);
