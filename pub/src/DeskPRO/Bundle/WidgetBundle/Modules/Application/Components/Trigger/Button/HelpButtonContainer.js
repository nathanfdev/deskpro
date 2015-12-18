import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { AgentMessagePopupContainer } from '../Popups/AgentMessage/AgentMessagePopupContainer';
import { ReplyButtons } from '../Popups/AgentMessage/ReplyButtons';
import { ReplyForm } from '../Popups/AgentMessage/ReplyForm';
import { windowResize } from '../../../Actions/dpWindowActions';
import { helpButtonSizeSelector, helpPopupSelector } from '../../../Selectors/dpWindow';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { OnlineAgentsContainer } from '../Popups/OnlineAgentsContainer';

@connect(state => ({
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    onClick: PropTypes.func,
    popup: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      popupShown: false
    };
  }

  onButtonClick = () => {
    const { dispatch, popup, onClick } = this.props;

    if (!popup || popup === 'none') {
      onClick();
    } else {
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

    this.props.dispatch(windowResize());
  };

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
      case 'agents':
      default:
        return <OnlineAgentsPopup {...popupProps} />;
    }
  }

  render() {
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
        <HelpButton {...this.props} onClick={this.onButtonClick} />
      </div>
    );
  }
}
