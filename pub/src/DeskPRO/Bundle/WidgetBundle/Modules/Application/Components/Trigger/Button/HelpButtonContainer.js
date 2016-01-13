import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import { openTriggerPopup, closeTriggerPopup } from '../../../Actions/dpWindowActions';
import { loadOnlineAgents } from '../../../Actions/peopleActions';
import { onlineAgentsCountSelector } from '../../../RecordStores/Selectors/peopleSelectors';
import {
  widgetHasChatSelector,
  widgetOpenedSelector,
  widgetPositionSelector,
  helpButtonSizeSelector,
  helpButtonNameSelector,
  helpButtonBackgroundColorSelector,
  helpButtonBorderColorSelector,
  helpButtonTextColorSelector,
  helpPopupTitleSelector,
  helpPopupMessageSelector,
  helpPopupReplyTypeSelector,
  agentPollingTimeoutSelector,
  triggerPopupOpenedSelector,
  liveDemoSelector
} from '../../../Selectors/dpWindow';

@connect(state => ({
  hasChat: widgetHasChatSelector(state),
  triggerPopupOpened: triggerPopupOpenedSelector(state),
  widgetOpened: widgetOpenedSelector(state),
  widgetPosition: widgetPositionSelector(state),
  size: helpButtonSizeSelector(state),
  name: helpButtonNameSelector(state),
  backgroundColor: helpButtonBackgroundColorSelector(state),
  textColor: helpButtonTextColorSelector(state),
  borderColor: helpButtonBorderColorSelector(state),
  helpPopupTitle: helpPopupTitleSelector(state),
  helpPopupMessage: helpPopupMessageSelector(state),
  helpPopupReplyType: helpPopupReplyTypeSelector(state),
  agentsCount: onlineAgentsCountSelector(state),
  agentPollingTimeout: agentPollingTimeoutSelector(state),
  liveDemo: liveDemoSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    hasChat: PropTypes.bool,
    triggerPopupOpened: PropTypes.bool,
    widgetOpened: PropTypes.bool,
    widgetPosition: PropTypes.string,
    dispatch: PropTypes.func,
    onClick: PropTypes.func,
    helpPopupTitle: PropTypes.string,
    helpPopupMessage: PropTypes.string,
    helpPopupReplyType: PropTypes.string,
    backgroundColor: PropTypes.string,
    textColor: PropTypes.string,
    borderColor: PropTypes.string,
    agentsCount: PropTypes.number,
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

  onClosePopup = () => {
    this.props.dispatch(closeTriggerPopup());
    localStorage['dpWidget.dpWindow.popupShown'] = 'none';
  };

  checkRenderPopup() {
    const { widgetOpened, triggerPopupOpened, agentsCount, hasChat, liveDemo, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';
    const notClosedPopup = !(storageKey in localStorage) || localStorage[storageKey] !== 'none';

    if (!widgetOpened && hasChat && (liveDemo || (notClosedPopup && agentsCount > 0))) {
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
    const { widgetPosition, helpPopupTitle, helpPopupMessage, helpPopupReplyType, onClick } = this.props;
    const { backgroundColor, textColor, borderColor } = this.props;
    const popupProps = {
      widgetPosition,
      backgroundColor,
      textColor,
      borderColor,
      onClick,
      onClose: this.onClosePopup
    };

    if (helpPopupTitle && helpPopupMessage) {
      return (
        <AgentMessagePopupContainer {...popupProps}>
          {helpPopupReplyType === 'buttons'
            ? <ReplyButtons {...popupProps} />
            : <ReplyForm {...popupProps} />
          }
        </AgentMessagePopupContainer>
      );
    }

    return <OnlineAgentsPopup {...popupProps} />;
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
