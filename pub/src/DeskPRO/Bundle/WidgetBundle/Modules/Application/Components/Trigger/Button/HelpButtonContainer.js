import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { loadOnlineAgents } from '../../../Actions/agentActions';
import { onlineAgentsCountSelector } from '../../../Selectors/agent';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import { openTriggerPopup, closeTriggerPopup } from '../../../Actions/dpWindowActions';
import {
  widgetOpenedSelector,
  helpButtonSizeSelector,
  helpPopupSelector,
  agentPollingTimeoutSelector,
  triggerPopupOpenedSelector
} from '../../../Selectors/dpWindow';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state),
  agentsCounts: onlineAgentsCountSelector(state),
  agentPollingTimeout: agentPollingTimeoutSelector(state),
  triggerPopupOpened: triggerPopupOpenedSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
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

  onOpenWidget = () => {
    const { agentsCounts, onClick } = this.props;
    if (!agentsCounts) {
      return;
    }

    onClick();
  };

  onClosePopup = () => {
    this.props.dispatch(closeTriggerPopup());
  };

  checkRenderPopup() {
    const { triggerPopupOpened, agentsCounts, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';

    if ((!(storageKey in localStorage) || localStorage[storageKey] !== 'none') && agentsCounts > 0) {
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
    const { popup } = this.props;
    const popupProps = {
      onClick: this.onOpenWidget,
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
    const { triggerPopupOpened, agentsCounts } = this.props;

    return (
      <div>
        {triggerPopupOpened &&
          <ClickOut onClickOut={this.onClosePopup}
                    context={[parent.document, window.triggerFrame.document]}>

            <OnlineAgentsContainer>
              {this.renderPopup()}
            </OnlineAgentsContainer>
          </ClickOut>
        }
        <HelpButton {...this.props} onClick={this.onOpenWidget} disabled={!agentsCounts} />
      </div>
    );
  }
}
