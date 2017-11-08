import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import { reopenWidget, openTriggerPopup, closeTriggerPopup } from '../../../Actions/dpWindowActions';
import { loadOnlineAgents } from '../../../Actions/peopleActions';
import { onlineAgentsCountSelector } from '../../../Selectors/peopleSelectors';
import {
  widgetProactiveChatSelector,
  widgetOpenedSelector,
  widgetPositionSelector,
  widgetPopupStyleSelector,
  widgetPopupDelaySelector,
  helpButtonSizeSelector,
  helpButtonNameSelector,
  helpButtonBackgroundColorSelector,
  helpButtonTextColorSelector,
  agentPollingTimeoutSelector,
  triggerPopupOpenedSelector,
  liveDemoSelector,
  helpPopupStartButtonSelector
} from '../../../Selectors/dpWindow';
import { widgetHasChatSelector } from '../../../Selectors/bootstrap';
import { chatIdSelector } from '../../../../Chat/Selectors/chat';
import { getLocation } from '../../../../../Services/history';

@connect(state => ({
  hasChat:              widgetHasChatSelector(state),
  proactiveChat:        widgetProactiveChatSelector(state),
  popupStyle:           widgetPopupStyleSelector(state),
  popupDelay:           widgetPopupDelaySelector(state),
  triggerPopupOpened:   triggerPopupOpenedSelector(state),
  widgetOpened:         widgetOpenedSelector(state),
  widgetPosition:       widgetPositionSelector(state),
  size:                 helpButtonSizeSelector(state),
  name:                 helpButtonNameSelector(state),
  backgroundColor:      helpButtonBackgroundColorSelector(state),
  textColor:            helpButtonTextColorSelector(state),
  agentsCount:          onlineAgentsCountSelector(state),
  agentPollingTimeout:  agentPollingTimeoutSelector(state),
  liveDemo:             liveDemoSelector(state),
  chatId:               chatIdSelector(state),
  helpPopupStartButton: helpPopupStartButtonSelector(state)
}))
export default class HelpButtonContainer extends React.Component {

  static propTypes = {
    hasChat:              PropTypes.bool,
    proactiveChat:        PropTypes.bool,
    triggerPopupOpened:   PropTypes.bool,
    widgetOpened:         PropTypes.bool,
    popupStyle:           PropTypes.string,
    popupDelay:           PropTypes.number,
    size:                 PropTypes.string,
    widgetPosition:       PropTypes.string,
    dispatch:             PropTypes.func,
    backgroundColor:      PropTypes.string,
    borderColor:          PropTypes.string,
    textColor:            PropTypes.string,
    agentsCount:          PropTypes.number,
    helpPopupStartButton: PropTypes.string,
    agentPollingTimeout:  PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    liveDemo: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      locationPath: false
    };
  }

  componentDidMount() {
    this.checkRenderPopup();
    this.pollingRequest();
  }

  componentWillUpdate() {
    getLocation((location) => {
      if (this.state.locationPath !== location.pathname) {
        this.setState({
          locationPath: location.pathname
        });
      }
    });
  }

  componentDidUpdate() {
    this.checkRenderPopup();
  }

  onClick = () => {
    this.props.dispatch(reopenWidget());
    if (storageAvailable('sessionStorage')) {
      delete sessionStorage['dpWidget.dpWindow.minimized'];
    }
  };

  onClosePopup = () => {
    this.props.dispatch(closeTriggerPopup());
    if (storageAvailable('sessionStorage')) {
      sessionStorage['dpWidget.dpWindow.popupShown'] = 'none';
    }
  };

  checkRenderPopup() {
    const { widgetOpened, triggerPopupOpened, agentsCount, hasChat, proactiveChat, liveDemo, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';
    const notClosedPopup = !storageAvailable('sessionStorage') || !(storageKey in sessionStorage) || sessionStorage[storageKey] !== 'none';

    if (!widgetOpened && hasChat && proactiveChat && (liveDemo || (notClosedPopup && agentsCount > 0))) {
      if (!triggerPopupOpened) {
        const delay = Math.abs(parseFloat(this.props.popupDelay)) || 0;
        setTimeout(() => dispatch(openTriggerPopup), delay * 1000);
      }
    } else if (triggerPopupOpened) {
      dispatch(closeTriggerPopup());
    }
  }

  pollingRequest() {
    const { widgetOpened, dispatch, agentPollingTimeout, liveDemo } = this.props;
    if (agentPollingTimeout === 'off' || !agentPollingTimeout || liveDemo) {
      return;
    }

    // Save polling loop if widget opened but don't send requests
    const onResponse = () => {
      setTimeout(() => this.pollingRequest(), agentPollingTimeout * 1000);
    };

    if (widgetOpened) {
      onResponse();
    } else {
      const promise = dispatch(loadOnlineAgents());
      promise.then(onResponse, onResponse);
    }
  }

  renderPopup() {
    const { widgetPosition } = this.props;
    const { backgroundColor, textColor, borderColor, liveDemo, popupStyle, size, helpPopupStartButton } = this.props;
    const popupProps = {
      widgetPosition,
      backgroundColor,
      textColor,
      borderColor,
      liveDemo,
      popupStyle,
      size,
      helpPopupStartButton,
      onClick:   this.onClick,
      onClose:   this.onClosePopup,
      getButton: () => this.button.node
    };

    if (popupStyle === 'agents_button') {
      return <OnlineAgentsPopup {...popupProps} />;
    }

    return (
      <AgentMessagePopupContainer {...popupProps}>
        {popupStyle && popupStyle.match(/_button$/) ? <ReplyButtons {...popupProps} /> : <ReplyForm {...popupProps} />}
      </AgentMessagePopupContainer>
    );
  }

  render() {
    const { triggerPopupOpened } = this.props;
    return (<div>
      {triggerPopupOpened &&
        <OnlineAgentsContainer>
          {this.renderPopup()}
        </OnlineAgentsContainer>
      }
      <HelpButton
        {...this.props}
        ref={(c) => { this.button = c; }}
        locationPath={this.state.locationPath || null}
        onClick={this.onClick}
      />
    </div>);
  }
}
