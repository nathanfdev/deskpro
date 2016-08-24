import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/onboardingActions';
import * as Tours from '../Tours';
import Joyride  from 'react-joyride';
import $ from 'jquery';
import moment from 'moment';
import Isvg from 'react-inlinesvg';

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
      currentStep:  0,
      intro:        false
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
        this.startOnboarding(!config.intro);
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

  getIntro = () => {
    const { intro, currentStep } = this.state;
    if (!intro || currentStep > 0) {
      return null;
    }
    const width = $(window).width();
    const height = $(window).height();
    const style = { left: width / 2 - 280, top: height / 2 - 320 };
    return (<div className="joyride">
      <div className="joyride-overlay" style={{ height }}>
        <div className="joyride-hole"></div>
        <div className="joyride-intro" style={style}>
          <img src={intro.img} role="presentation" />
          <h3>{intro.title}</h3>
          <p>{intro.text}</p>
          <footer><button className="ui button" onClick={this.closeIntro}>{intro.action}</button></footer>
        </div>
      </div>
    </div>);
  };

  closeIntro = () => {
    this.setState({ intro: false });
    this.resumeOnboarding();
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
            date_completion: moment().unix()
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
          onboarding.date_completion = moment().unix();
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
    const svgSrc = window.DESKPRO_APP_ASSETS_URL.replace(/\/$/, '');
    return (<div>
      <Joyride
        ref="joyride"
        steps={this.state.steps}
        showSkipButton={false}
        locale={{
          back:  (<Isvg src={`${svgSrc}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/onboarding/back-arrow.svg`} />),
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
