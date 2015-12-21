import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { loadOnlineAgents } from '../../../Actions/agentActions';
import { windowResize } from '../../../Actions/dpWindowActions';
import { widgetOpenedSelector, helpButtonSizeSelector, helpPopupSelector } from '../../../Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../../../Selectors/agent';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state),
  agentsCounts: onlineAgentsCountSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    widgetOpened: PropTypes.bool,
    dispatch: PropTypes.func,
    onClick: PropTypes.func,
    popup: PropTypes.string,
    agentsCounts: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      popupShown: false
    };
  }

  componentDidMount() {
    const { agentsCounts } = this.props;
    const storageKey = 'widget.dpWindow.popupShown';

    if (!(storageKey in localStorage) || localStorage[storageKey] !== 'none' && agentsCounts > 0) {
      setTimeout(() => this.setState({popupShown: true}), 0);
    }

    this.pollingRequest();
  }

  onButtonClick = () => {
    const { dispatch, popup, agentsCounts, onClick } = this.props;

    if (!agentsCounts) {
      return;
    }

    if (!popup || popup === 'none') {
      onClick();
    } else {
      localStorage['widget.dpWindow.popupShown'] = 'true';
      this.setState({
        popupShown: true
      });

      dispatch(windowResize());
    }
  };

  onPopupClick = () => {
    this.setState({
      popupShown: false
    });

    this.props.onClick();
    this.props.dispatch(windowResize());
  };

  onClosePopup = () => {
    this.setState({
      popupShown: false
    });

    localStorage['widget.dpWindow.popupShown'] = 'none';
    this.props.dispatch(windowResize());
  };

  pollingRequest() {
    const { widgetOpened, dispatch } = this.props;
    if (widgetOpened) {
      return;
    }

    const promise = dispatch(loadOnlineAgents());
    const onResponse = () => {
      setTimeout(() => this.pollingRequest(), 10000);
    };

    promise.then(onResponse, onResponse);
  }

  renderPopup() {
    const { popup } = this.props;
    const popupProps = {
      onClick: this.onPopupClick,
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
        <HelpButton {...this.props} onClick={this.onButtonClick} disabled={!agentsCounts} />
      </div>
    );
  }
}
