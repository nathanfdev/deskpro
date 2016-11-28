import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Joyride  from 'react-joyride';
import $ from 'jquery';
import moment from 'moment';
import Isvg from 'react-inlinesvg';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/onboardingActions';
import * as Tours from '../Tours';

@connect(state => ({
  onboardings: collectionSelectorFactory('Onboarding', 'pending')(state)
}))
export class AgentOnboardingContainer extends SeparateComponent {
  static propTypes = {
    onboardings: PropTypes.object.isRequired,
    dispatch:    PropTypes.func.isRequired
  };

  static getType() {
    return 'AgentOnboardingContainer';
  }

  pauseOnboarding = (callback) => {
    this.props.dispatch(actions.pauseOnboarding({ callback, active: true }));
  };

  resumeOnboarding = () => {
    this.props.dispatch(actions.resumeOnboarding());
  };

  updateCurrentStep = (onboardingId, onboarding) => {
    this.props.dispatch(actions.updateCurrentStep(onboardingId, onboarding));
  };

  render() {
    let result = <div />;
    const props = {
      pauseOnboarding:   this.pauseOnboarding,
      resumeOnboarding:  this.resumeOnboarding,
      updateCurrentStep: this.updateCurrentStep
    };

    if (this.props.onboardings.size) {
      this.props.onboardings.map((onboarding) => {
        result = <AgentOnboarding onboarding={onboarding} {...props} />;
        return true;
      });
    }
    return result;
  }
}

export class AgentOnboarding extends React.Component {
  static propTypes = {
    onboarding:        PropTypes.object.isRequired,
    pauseOnboarding:   PropTypes.func.isRequired,
    resumeOnboarding:  PropTypes.func.isRequired,
    updateCurrentStep: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      steps:        [],
      onboardingId: 0,
      type:         'continuous',
      force:        false,
      currentStep:  0,
      intro:        false
    };
  }

  componentDidMount() {
    const { onboarding, pauseOnboarding } = this.props;

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
        this.startOnboarding(!config.intro);
      } else {
        this.setStep(onboarding.get('current_step'));
        this.startOnboarding();
        pauseOnboarding(this.resumeOnboarding);
      }
    }
    return true;
  }

  componentDidUpdate(prevProps, prevState) {
    if (!prevState.ready && this.state.ready) {
      this.joyride.start(true);
    }
  }

  setStep = (step) => {
    this.setState({ currentStep: step });
  };

  getIntro = () => {
    const { intro, currentStep } = this.state;
    if (!intro || currentStep > 0) {
      return null;
    }

    const width = $(window).width();
    const height = $(window).height();
    const style = { left: (width / 2) - 280, top: (height / 2) - 320 };
    return (
      <div className="joyride">
        <div className="joyride-overlay" style={{ height }}>
          <div className="joyride-hole" />
          <div className="joyride-intro" style={style}>
            <img src={intro.img} role="presentation" />
            <h3>{agentPhrases.get(intro.title)}</h3>
            <p>{agentPhrases.get(intro.text)}</p>
            <footer>
              <button className="ui button" onClick={this.closeIntro}>{agentPhrases.get(intro.action)}</button>
            </footer>
          </div>
        </div>
      </div>
    );
  };

  closeIntro = () => {
    this.setState({ intro: false });
    this.resumeOnboarding();
  };

  addSteps = (steps) => {
    const joyride = this.joyride;

    let stepsArray = steps;
    if (!Array.isArray(stepsArray)) {
      stepsArray = [steps];
    }

    stepsArray = stepsArray.map((step) => {
      const newStep = step;
      newStep.title = agentPhrases.get(step.title);
      newStep.text = agentPhrases.get(step.text);
      return newStep;
    });

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
    this.joyride.toggleTooltip(true, this.state.currentStep);
  };

  startOnboarding = (open) => {
    this.joyride.start(open);
  };

  callback = (data) => {
    const joyride = this.joyride;
    const progress = joyride.getProgress();
    let onboarding;

    switch (data.action) {
      case 'close':
        if (progress.percentageComplete < 100) {
          this.props.pauseOnboarding(this.resumeOnboarding);
        } else {
          this.finishOnboarding(progress.index);
        }
        break;
      case 'next':
      case 'back':
        onboarding = {
          current_step: progress.index
        };
        this.setState({ currentStep: progress.index, intro: false });
        if (progress.percentageComplete === 0) {
          onboarding.status = 0;
        } else if (progress.percentageComplete === 100) {
          onboarding.status = 2;
          onboarding.date_completion = moment().format();
        } else {
          onboarding.status = 1;
        }
        this.props.updateCurrentStep(this.state.onboardingId, onboarding);
        break;
      case 'beacon':
        this.props.resumeOnboarding();
        break;
      case 'finished':
        this.finishOnboarding(progress.index);
        break;
      default:
        break;
    }
  };

  finishOnboarding = (index) => {
    const onboarding = {
      current_step:    index,
      status:          2,
      date_completion: moment().format()
    };
    this.props.updateCurrentStep(this.state.onboardingId, onboarding);
  };

  render() {
    const { type, force } = this.state;
    const props = {
      type,
      disableOverlay: force
    };
    const back = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/onboarding/back-arrow.svg`;
    return (<div>
      <Joyride
        ref={(c) => { this.joyride = c; }}
        steps={this.state.steps}
        showSkipButton={false}
        locale={{
          back: (
            <Isvg
              src={back}
            />
           ),
          close: (<span>Close</span>),
          last:  (<span>Finish</span>),
          next:  (<span>Next</span>)
        }}
        callback={this.callback}
        showStepsProgress
        tooltipOffset={5}
        {...props}
      />
      {this.getIntro()}
    </div>);
  }
}
