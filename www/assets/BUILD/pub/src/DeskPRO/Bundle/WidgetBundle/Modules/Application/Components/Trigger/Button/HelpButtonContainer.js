import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import { openWidget, openTriggerPopup, closeTriggerPopup } from '../../../Actions/dpWindowActions';
import { loadOnlineAgents } from '../../../Actions/peopleActions';
import { onlineAgentsCountSelector } from '../../../Selectors/peopleSelectors';
import {
  widgetProactiveChatSelector,
  widgetOpenedSelector,
  widgetPositionSelector,
  widgetPopupStyleSelector,
  helpButtonSizeSelector,
  helpButtonNameSelector,
  helpButtonBackgroundColorSelector,
  helpButtonTextColorSelector,
  agentPollingTimeoutSelector,
  triggerPopupOpenedSelector,
  liveDemoSelector
} from '../../../Selectors/dpWindow';

import { widgetHasChatSelector } from '../../../Selectors/bootstrap';

@connect(state => ({
  hasChat:             widgetHasChatSelector(state),
  proactiveChat:       widgetProactiveChatSelector(state),
  popupStyle:          widgetPopupStyleSelector(state),
  triggerPopupOpened:  triggerPopupOpenedSelector(state),
  widgetOpened:        widgetOpenedSelector(state),
  widgetPosition:      widgetPositionSelector(state),
  size:                helpButtonSizeSelector(state),
  name:                helpButtonNameSelector(state),
  backgroundColor:     helpButtonBackgroundColorSelector(state),
  textColor:           helpButtonTextColorSelector(state),
  agentsCount:         onlineAgentsCountSelector(state),
  agentPollingTimeout: agentPollingTimeoutSelector(state),
  liveDemo:            liveDemoSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    hasChat:             PropTypes.bool,
    proactiveChat:       PropTypes.bool,
    triggerPopupOpened:  PropTypes.bool,
    widgetOpened:        PropTypes.bool,
    popupStyle:          PropTypes.string,
    widgetPosition:      PropTypes.string,
    dispatch:            PropTypes.func,
    backgroundColor:     PropTypes.string,
    borderColor:         PropTypes.string,
    textColor:           PropTypes.string,
    agentsCount:         PropTypes.number,
    agentPollingTimeout: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    liveDemo: PropTypes.bool
  };

  componentDidMount() {
    this.checkRenderPopup();
    this.pollingRequest();
  }

  componentDidUpdate() {
    this.checkRenderPopup();
  }

  onClick = () => {
    this.props.dispatch(openWidget());
  };

  onClosePopup = () => {
    this.props.dispatch(closeTriggerPopup());
    localStorage['dpWidget.dpWindow.popupShown'] = 'none';
  };

  checkRenderPopup() {
    const { widgetOpened, triggerPopupOpened, agentsCount, hasChat, proactiveChat, liveDemo, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';
    const notClosedPopup = !(storageKey in localStorage) || localStorage[storageKey] !== 'none';

    if (!widgetOpened && hasChat && proactiveChat && (liveDemo || (notClosedPopup && agentsCount > 0))) {
      if (!triggerPopupOpened) {
        dispatch(openTriggerPopup());
      }
    } else {
      if (triggerPopupOpened) {
        dispatch(closeTriggerPopup());
      }
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
    const { backgroundColor, textColor, borderColor, liveDemo, popupStyle } = this.props;
    const popupProps = {
      widgetPosition,
      backgroundColor,
      textColor,
      borderColor,
      liveDemo,
      popupStyle,
      onClick: this.onClick,
      onClose: this.onClosePopup
    };

    if (popupStyle !== 'agents_button') {
      return (
        <AgentMessagePopupContainer {...popupProps}>
          {popupStyle.match(/_button$/)
            ? <ReplyButtons {...popupProps} />
            : <ReplyForm {...popupProps} />
          }
        </AgentMessagePopupContainer>
      );
    }

    return <OnlineAgentsPopup {...popupProps} />;
  }

  render() {
    const { triggerPopupOpened } = this.props;

    return (
      <div>
        {triggerPopupOpened &&
          <OnlineAgentsContainer>
            {this.renderPopup()}
          </OnlineAgentsContainer>
        }
        <HelpButton {...this.props} onClick={this.onClick} />
      </div>
    );
  }
}
