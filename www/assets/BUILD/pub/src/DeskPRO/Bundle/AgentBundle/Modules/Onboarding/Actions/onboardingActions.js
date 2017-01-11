import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const updateCurrentStep = createAction(
  'ONBOARDING_UPDATE_CURRENT_STEP',
  (onboardingId, params) => new Promise(
    (resolve, reject) => {
      if (onboardingId) {
        return repository('Onboarding').saveProgress(params, onboardingId)
          .success(() => resolve())
          .error(response => reject(response));
      }
      return false;
    }
  )
);

export const pauseOnboarding = createAction('ONBOARDING_PAUSE');
export const resumeOnboarding = createAction(
  'ONBOARDING_RESUME',
  () => {}
);
