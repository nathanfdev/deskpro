import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class OnboardingRepository extends ApiRepository {
  loadNewOnboarding() {
    return this.api.sendGet(`DP_API/${this.url}/new`);
  }

  saveProgress(onboarding, id) {
    return this.api.sendPut(`DP_API/${this.url}/${id}`, onboarding);
  }
}
export default OnboardingRepository;
