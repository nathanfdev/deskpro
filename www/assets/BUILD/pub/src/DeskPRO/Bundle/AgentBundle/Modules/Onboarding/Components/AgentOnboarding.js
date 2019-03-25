import $ from 'jquery';
import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage, FormattedHTMLMessage } from 'react-intl';
import { connect } from 'react-redux';
import Joyride  from 'react-joyride';
import moment from 'moment';
import Isvg from 'react-inlinesvg';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
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

  checkTimezone = () => {
    if (window.DESKPRO_TIME_OUT_OF_SYNC) {
      window.DESKPRO_TIME_OUT_OF_SYNC = false;
      window.$.ajax({
        url:      `${window.BASE_URL}agent/misc/get-server-time`,
        dataType: 'json',
        success(data) {
          window.$('#time_outofsync').find('.server_time').text(data.time_formatted);

          const nowTs = ((new Date()).getTime() / 1000) - (new Date().getTimezoneOffset() * 60);
          const diff = Math.abs(nowTs - data.timestamp);

          if (diff > 1200) {
            window.DESKPRO_TIME_OUT_OF_SYNC = diff;
            console.log('(Recheck) Time is off by %s seconds', diff);

            if (window.DESKPRO_TIME_OUT_OF_SYNC_IGNORE
              && Math.abs(diff - window.DESKPRO_TIME_OUT_OF_SYNC_IGNORE) < 480) {
              window.DESKPRO_TIME_OUT_OF_SYNC = null;
              console.log('(Recheck) Time offset is ignored');
            }
          }

          console.log(window.DESKPRO_TIME_OUT_OF_SYNC);
          if (window.DESKPRO_TIME_OUT_OF_SYNC) {
            window.$('#time_outofsync').trigger('dp_open');
          }
        }
      });
    }
  };

  componentDidMount() {
    if (!this.props.onboardings.size || this.props.onboardings.first().get('status') > 0) {
      this.checkTimezone();
    }
  }


  render() {
    let result = <div />;
    const props = {
      pauseOnboarding:   this.pauseOnboarding,
      resumeOnboarding:  this.resumeOnboarding,
      updateCurrentStep: this.updateCurrentStep
    };

    if (this.props.onboardings.size) {
      this.props.onboardings.map((onboarding) => {
        result = <AgentOnboarding onboarding={onboarding} {...props} postOnboardingCallback={this.checkTimezone} />;
        return true;
      });
    }
    return result;
  }
}

export class AgentOnboarding extends React.Component {
  static propTypes = {
    onboarding:             PropTypes.object.isRequired,
    pauseOnboarding:        PropTypes.func.isRequired,
    resumeOnboarding:       PropTypes.func.isRequired,
    updateCurrentStep:      PropTypes.func.isRequired,
    postOnboardingCallback: PropTypes.func.isRequired
  };
  static defaultProps = {
    postOnboardingCallback() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      steps:        [],
      onboardingId: 0,
      type:         'continuous',
      force:        false,
      currentStep:  0,
      intro:        false,
      status:       0
    };
    this.interval = null;
    this.retries = 0;
  }

  componentDidMount() {
    const { onboarding, pauseOnboarding } = this.props;

    const config = this.getConfig();

    if (config) {
      this.addSteps(config.steps);
      delete config.steps;
      if (config) {
        this.loadConfig(config);
      }
      const status = onboarding.get('status');
      if (!status) {
        this.startOnboarding(!config.intro, config.waitFor);
      } else {
        this.setStep(onboarding.get('current_step'), config.waitFor);
        this.setStatus(status);
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

  getConfig() {
    const { onboarding } = this.props;
    const object = onboarding.get('onboarding_class');

    let config = null;
    if (Tours[object]) {
      config = Tours[object];
      config.onboardingId = onboarding.get('id');
    } else {
      config = onboarding.get('config');
    }

    return config;
  }

  setStep = (step) => {
    this.setState({ currentStep: step });
  };

  setStatus = (status) => {
    this.setState({ status });
  };

  getIntro = () => {
    const { intro, status } = this.state;
    if (!intro || status) {
      return null;
    }

    const bodyStyle = window.getComputedStyle(document.body);
    const width = parseInt(bodyStyle.width, 10);
    const height = parseInt(bodyStyle.height, 10);
    const style = { left: (width / 2) - 280, top: (height / 2) - 320 };
    return (
      <div className="joyride">
        <div className="joyride-overlay" style={{ height }} onClick={this.closeIntro}>
          <div className="joyride-hole" />
          <div className="joyride-intro" style={style} onClick={this.eventStopPropagation}>
            <img src={intro.img} role="presentation" />
            <h3><FormattedMessage id={intro.title} /></h3>
            {intro.html ? <div className={`body ${(intro.bodyType || '')}`}><FormattedHTMLMessage id={intro.html} /></div> : <p><FormattedMessage id={intro.text} /></p>}
            <footer>
              <button className="ui button" onClick={this.closeIntro}><FormattedMessage id={intro.action} /></button>
            </footer>
          </div>
        </div>
      </div>
    );
  };

  eventStopPropagation = (ev) => {
    ev.stopPropagation();
  };

  closeIntro = () => {
    this.setState({ intro: false });

    if (!this.state.steps || this.state.steps.length === 0) {
      this.finishOnboarding();
    } else {
      this.resumeOnboarding();
    }
  };

  addSteps = (steps) => {
    if (!steps) {
      return;
    }
    let stepsArray = steps;
    if (!Array.isArray(stepsArray)) {
      stepsArray = [steps];
    }

    stepsArray = stepsArray.map((step) => {
      const newStep = step;
      newStep.title = <FormattedMessage id={step.title} />;
      newStep.text = <FormattedMessage id={step.text} />;
      return newStep;
    });

    if (!stepsArray.length) {
      return;
    }

    this.setState((currentState) => {
      const result = {};
      result.steps = currentState.steps.concat(stepsArray);
      return result;
    });
  };

  loadConfig = (config) => {
    this.setState(config);
  };

  resumeOnboarding = () => {
    this.joyride.toggleTooltip({ show: true, index: this.state.currentStep, action: 'jump' });
  };

  waitFor = (waitFor, open) => {
    this.retries = this.retries + 1;
    if ($(waitFor).length) {
      this.joyride.start(open);
      clearInterval(this.interval);
    } else if (this.retries > 4) {
      // stop trying to start onboarding
      clearInterval(this.interval);
    }
  };

  startOnboarding = (open, waitFor = false) => {
    if (waitFor) {
      this.interval = setInterval(() => this.waitFor(waitFor, open), (this.retries + 1) * 1000);
    } else {
      this.joyride.start(open);
    }
  };

  callback = (data) => {
    let index = data.index;
    if (data.action === 'autostart' || data.type === 'step:after') {
      if (data.type === 'step:after') {
        index += 1;
      }
      const percentageComplete = Math.round((index / this.state.steps.length) * 100);
      let onboarding;

      switch (data.action) {
        case 'close':
          if (percentageComplete < 100) {
            this.props.pauseOnboarding(this.resumeOnboarding);
          } else {
            this.finishOnboarding(index);
          }
          break;
        case 'autostart':
        case 'next':
        case 'back':
          onboarding = {
            current_step: index
          };
          this.setState({ currentStep: index, intro: false });
          if (percentageComplete === 100) {
            onboarding.status = 2;
            onboarding.date_completion = moment().format();
            this.finishOnboarding(index);
            break;
          } else {
            onboarding.status = 1;
          }
          this.props.updateCurrentStep(this.state.onboardingId, onboarding);
          break;
        case 'beacon':
          this.props.resumeOnboarding();
          break;
        case 'finished':
          this.finishOnboarding(index);
          break;
        default:
          break;
      }
    }
  };

  finishOnboarding = (index) => {
    const onboarding = {
      current_step:    index,
      status:          2,
      date_completion: moment().format()
    };
    this.props.updateCurrentStep(this.state.onboardingId, onboarding);
    this.props.postOnboardingCallback();
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
