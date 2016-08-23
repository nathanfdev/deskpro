import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import { updateCurrentStep } from '../Actions/OnboardingActions';
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
    this.props.onboardings.map((onboarding) => {
      result = <AgentOnboarding onboarding={onboarding} />;
      return true;
    });
    return result;
  }
}


export class AgentOnboarding extends React.Component {
  static propTypes = {
    onboarding: PropTypes.object.isRequired
  };

  constructor() {
    super();
    this.state = {
      steps:        [],
      onboardingId: 0,
      type:         'continuous',
      force:        false
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
      this.startOnboarding();
    }
    return true;
  }

  componentDidUpdate(prevProps, prevState) {
    if (!prevState.ready && this.state.ready) {
      console.log('start');
      this.refs.joyride.start(true);
    }
  }

  loadConfig = (config) => {
    this.setState(config);
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

  startOnboarding = () => {
    this.refs.joyride.start(true);
  };

  callback = (data) => {
    const joyride = this.refs.joyride;
    const progress = joyride.getProgress();
    let onboarding;

    switch (data.type) {
      case 'close':
        if (progress.percentageComplete < 100) {
          console.log('Close not finished');
        } else {
          console.log('Close finished');
        }
        break;
      case 'step:after':
        onboarding = {
          current_step: progress.index
        };
        updateCurrentStep(this.state.onboardingId, onboarding);
        break;
      case 'finished':
        break;
      default:
        console.log('%ccallback', 'color: #47AAAC; font-weight: bold; font-size: 13px;'); // eslint-disable-line no-console
        console.log(data); // eslint-disable-line no-console
        break;
    }
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
          last:  (<span>Last</span>),
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
