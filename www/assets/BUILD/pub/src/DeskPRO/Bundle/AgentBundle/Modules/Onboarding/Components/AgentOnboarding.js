import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import { updateCurrentStep } from '../Actions/OnboardingActions';
import Joyride  from 'react-joyride';
import * as Tours from '../Tours';

@connect(state => ({
  onboardings: collectionSelectorFactory('Onboardings', 'new')(state)
}))
class AgentOnboarding extends SeparateComponent {
  static propTypes = {
    onboardings: PropTypes.object.isRequired
  };

  constructor() {
    super();
    this.state = {
      steps: [],
      type:  'continuous',
      force: false
    };
  }
  static getType() {
    return 'AgentOnboarding';
  }

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

  componentDidMount() {
    this.props.onboardings.map((onboarding) => {
      const object = onboarding.get('onboarding_class');
      if (Tours[object]) {
        const config = Tours[object].get();
        this.addSteps(config.steps);
        delete config.steps;
        console.log(config);
        if (config) {
          this.setState(config);
        }
        this.refs.joyride.start(true);
      }
      return true;
    });
  }

  componentDidUpdate(prevProps, prevState) {
    if (!prevState.ready && this.state.ready) {
      console.log('start');
      this.refs.joyride.start(true);
    }
  }

  callback = (data) => {
    const joyride = this.refs.joyride;

    console.log(joyride);
    console.log(joyride.getProgress());
    console.log(this.props.onboardings[0]);
    switch (data.type) {
      case 'close':
        if (data.step && data.step.length < this.state.steps.length) {
          console.log('Close not finished');
        } else {
          console.log('Close finished');
        }
        break;
      case 'step:after':
        updateCurrentStep();
        break;
      default:
        break;
    }
    console.log('%ccallback', 'color: #47AAAC; font-weight: bold; font-size: 13px;'); // eslint-disable-line no-console
    console.log(data); // eslint-disable-line no-console
  };

  render() {
    const { type, force } = this.state;
    console.log(this.state);
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
        debug
        {...props}
      />
    </div>);
  }
}
export default AgentOnboarding;
