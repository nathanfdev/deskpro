import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/onboardingActions';
import Joyride  from 'react-joyride';
import * as Tours from '../Tours';

@connect(state => ({
  onboardings: collectionSelectorFactory('Onboarding', 'new')(state)
}))
export class AgentOnboardingContainer extends SeparateComponent {
  static propTypes = {
    onboardings: PropTypes.object.isRequired
  };
  static getType() {
    return 'AgentOnboardingContainer';
  }

  render() {
    let result = <div />;
    if (this.props.onboardings.size) {
      this.props.onboardings.map((onboarding) => {
        result = <AgentOnboarding onboarding={onboarding} />;
        return true;
      });
    }
    return result;
  }
}

@connect()
export class AgentOnboarding extends React.Component {
  static propTypes = {
    onboarding: PropTypes.object.isRequired,
    dispatch:   PropTypes.func.isRequired
  };

  constructor() {
    super();
    this.state = {
      steps:        [],
      onboardingId: 0,
      type:         'continuous',
      force:        false,
      currentStep:  0
    };
  }

  componentDidMount() {
    const { onboarding } = this.props;

    const object = onboarding.get('onboarding_class');
    let config = null;
    if (Tours[object]) {
      config = Tours[object];
      config.onboardingId = onboarding.get('id');
    } else {
      config = onboarding.get('config');
    }
    if (config) {
      this.addSteps(config.steps);
      delete config.steps;
      if (config) {
        this.loadConfig(config);
      }
      if (!onboarding.get('status')) {
        this.startOnboarding(true);
      } else {
        this.setStep(onboarding.get('current_step'));
        this.startOnboarding();
        this.props.dispatch(actions.pauseOnboarding({ callback: this.resumeOnboarding, active: true }));
      }
    }
    return true;
  }

  componentDidUpdate(prevProps, prevState) {
    if (!prevState.ready && this.state.ready) {
      this.refs.joyride.start(true);
    }
  }

  setStep = (step) => {
    this.setState({ currentStep: step });
  };

  addSteps = (steps) => {
    const joyride = this.refs.joyride;

    let stepsArray = steps;
    if (!Array.isArray(stepsArray)) {
      stepsArray = [steps];
    }

    if (!stepsArray.length) {
      return false;
    }

    this.setState((currentState) => {
      const result = {};
      result.steps = currentState.steps.concat(joyride.parseSteps(stepsArray));
      return result;
    });
    return true;
  };

  loadConfig = (config) => {
    this.setState(config);
  };

  resumeOnboarding = () => {
    this.refs.joyride.toggleTooltip(true, this.state.currentStep);
  };

  startOnboarding = (open) => {
    this.refs.joyride.start(open);
  };

  callback = (data) => {
    const joyride = this.refs.joyride;
    const progress = joyride.getProgress();
    let onboarding;

    switch (data.action) {
      case 'close':
        if (progress.percentageComplete < 100) {
          this.props.dispatch(actions.pauseOnboarding({ callback: this.resumeOnboarding, active: true }));
        } else {
          onboarding = {
            current_step:    progress.index,
            status:          2,
            data_completion: new Date().toISOString()
          };
          this.props.dispatch(actions.updateCurrentStep(this.state.onboardingId, onboarding));
        }
        break;
      case 'next':
      case 'back':
        onboarding = {
          current_step: progress.index
        };
        this.setState({ currentStep: progress.index });
        if (progress.percentageComplete === 0) {
          onboarding.status = 0;
        } else if (progress.percentageComplete === 100) {
          onboarding.status = 2;
          onboarding.data_completion = new Date().toISOString();
        } else {
          onboarding.status = 1;
        }
        this.props.dispatch(actions.updateCurrentStep(this.state.onboardingId, onboarding));
        break;
      case 'beacon':
        this.props.dispatch(actions.resumeOnboarding());
        break;
      case 'finished':
        console.log('Close finished');
        break;
      default:
        break;
    }
    console.log('%ccallback', 'color: #47AAAC; font-weight: bold; font-size: 13px;'); // eslint-disable-line no-console
    console.log(data); // eslint-disable-line no-console
  };

  render() {
    const { type, force } = this.state;
    const props = {
      type,
      disableOverlay: force
    };
    return (<div>
      <Joyride
        ref="joyride"
        steps={this.state.steps}
        showSkipButton={false}
        locale={{
          back:  (<i className="fa fa-arrow-left" />),
          close: (<span>Close</span>),
          last:  (<span>Finish</span>),
          next:  (<span>Next</span>)
        }}
        callback={this.callback}
        showStepsProgress
        tooltipOffset={5}
        {...props}
      />
    </div>);
  }
}
