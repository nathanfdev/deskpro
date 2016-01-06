import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { loadOnlineAgents } from '../../../Actions/agentActions';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import { openTriggerPopup, closeTriggerPopup } from '../../../Actions/dpWindowActions';
import { onlineAgentsCountSelector } from '../../../Selectors/agent';
import {
  widgetOpenedSelector,
  helpButtonSizeSelector,
  helpButtonNameSelector,
  helpButtonBackgroundColorSelector,
  helpButtonBorderColorSelector,
  helpButtonTextColorSelector,
  helpPopupSelector,
  agentPollingTimeoutSelector,
  triggerPopupOpenedSelector,
  widgetHasChatSelector
} from '../../../Selectors/dpWindow';

@connect(state => ({
  hasChat: widgetHasChatSelector(state),
  triggerPopupOpened: triggerPopupOpenedSelector(state),
  widgetOpened: widgetOpenedSelector(state),
  size: helpButtonSizeSelector(state),
  name: helpButtonNameSelector(state),
  backgroundColor: helpButtonBackgroundColorSelector(state),
  textColor: helpButtonTextColorSelector(state),
  borderColor: helpButtonBorderColorSelector(state),
  popup: helpPopupSelector(state),
  agentsCounts: onlineAgentsCountSelector(state),
  agentPollingTimeout: agentPollingTimeoutSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    hasChat: PropTypes.bool,
    triggerPopupOpened: PropTypes.bool,
    widgetOpened: PropTypes.bool,
    dispatch: PropTypes.func,
    onClick: PropTypes.func,
    popup: PropTypes.string,
    agentsCounts: PropTypes.number,
    agentPollingTimeout: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ])
  };

  componentDidMount() {
    this.checkRenderPopup();
    this.pollingRequest();
  }

  componentDidUpdate() {
    this.checkRenderPopup();
  }

  onClosePopup = () => {
    this.props.dispatch(closeTriggerPopup());
  };

  checkRenderPopup() {
    const { triggerPopupOpened, agentsCounts, hasChat, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';

    if ((!(storageKey in localStorage) || localStorage[storageKey] !== 'none') && hasChat && agentsCounts > 0) {
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
    const { widgetOpened, dispatch, agentPollingTimeout } = this.props;
    if (widgetOpened) {
      return;
    }

    const promise = dispatch(loadOnlineAgents());
    const onResponse = () => {
      if (agentPollingTimeout !== 'off' && agentPollingTimeout > 0) {
        setTimeout(() => this.pollingRequest(), agentPollingTimeout * 1000);
      }
    };

    promise.then(onResponse, onResponse);
  }

  renderPopup() {
    const { popup, onClick } = this.props;
    const popupProps = {
      onClick: onClick,
      onClose: this.onClosePopup
    };

    switch (popup) {
      case 'replyMessageButtons':
        return (
          <AgentMessagePopupContainer {...popupProps}>
            <ReplyButtons {...popupProps} />
          </AgentMessagePopupContainer>
        );
      case 'replyMessageForm':
        return (
          <AgentMessagePopupContainer {...popupProps}>
            <ReplyForm {...popupProps} />
          </AgentMessagePopupContainer>
        );
      case 'onlineAgents':
      default:
        return <OnlineAgentsPopup {...popupProps} />;
    }
  }

  render() {
    const { triggerPopupOpened, onClick } = this.props;

    return (
      <div>
        {triggerPopupOpened &&
          <OnlineAgentsContainer>
            {this.renderPopup()}
          </OnlineAgentsContainer>
        }
        <HelpButton {...this.props} onClick={onClick} />
      </div>
    );
  }
}
