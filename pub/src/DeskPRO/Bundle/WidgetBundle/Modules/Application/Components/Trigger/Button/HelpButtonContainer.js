import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { loadOnlineAgents } from '../../../Actions/agentActions';
import { windowResize } from '../../../Actions/dpWindowActions';
import { onlineAgentsCountSelector } from '../../../Selectors/agent';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';
import {
  widgetOpenedSelector,
  helpButtonSizeSelector,
  helpPopupSelector,
  agentPollingTimeoutSelector
} from '../../../Selectors/dpWindow';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state),
  agentsCounts: onlineAgentsCountSelector(state),
  agentPollingTimeout: agentPollingTimeoutSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
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

  constructor(props) {
    super(props);
    this.state = {
      popupShown: false
    };
  }

  componentDidMount() {
    this.checkRenderPopup();
    this.pollingRequest();
  }

  componentDidUpdate() {
    this.checkRenderPopup();
  }

  onOpenWidget = () => {
    const { agentsCounts, onClick, dispatch } = this.props;

    if (!agentsCounts) {
      return;
    }

    onClick();
    dispatch(windowResize());
  };

  onClosePopup = () => {
    localStorage['dpWidget.dpWindow.popupShown'] = 'none';
    this.setState({
      popupShown: false
    });

    this.props.dispatch(windowResize());
  };

  checkRenderPopup() {
    const { agentsCounts, dispatch } = this.props;
    const storageKey = 'dpWidget.dpWindow.popupShown';

    if ((!(storageKey in localStorage) || localStorage[storageKey] !== 'none') && agentsCounts > 0) {
      if (!this.state.popupShown) {
        this.setState({
          popupShown: true
        });

        dispatch(windowResize());
      }
    } else {
      if (this.state.popupShown) {
        this.setState({
          popupShown: false
        });

        dispatch(windowResize());
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
    const { agentsCounts } = this.props;

    return (
      <div>
        {this.state.popupShown &&
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
