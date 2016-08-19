import Shepherd from 'tether-shepherd';

class TestOnboarding {
  static get() {
    const onboarding = new Shepherd.Tour({
      defaults: {
        classes:  'shepherd-theme-arrows',
        scrollTo: true
      }
    });

    onboarding.addStep('example-step', {
      text:     'This step is attached to the bottom of the <code>.example-css-selector</code> element.',
      attachTo: '#dp_content_info td h1',
      classes:  'example-step-extra-class',
      buttons:  [
        {
          text:   'Next',
          action: onboarding.next
        }
      ]
    });

    return onboarding;
  }
}
export default TestOnboarding;
