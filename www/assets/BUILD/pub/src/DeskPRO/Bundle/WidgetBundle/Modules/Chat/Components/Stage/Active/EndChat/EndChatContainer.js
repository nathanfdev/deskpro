import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { EndChatConfirmPopup } from './EndChatConfirmPopup';
import { endChat } from '../../../../Actions/chatActions';
import { chatIdSelector, lockedPollingSelector } from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  locked: lockedPollingSelector(state)
}))
export class EndChatContainer extends React.Component {

  static propTypes = {
    dispatch:        PropTypes.func,
    locked:          PropTypes.bool,
    chatId:          PropTypes.number,
    confirmPosition: PropTypes.string,
    children:        PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      confirmPopup: false
    };
  }

  onOpenPopup = event => {
    event.preventDefault();
    this.setState({
      confirmPopup: true
    });
  };

  onEndChat = event => {
    const { chatId, dispatch, locked } = this.props;
    if (locked) {
      return;
    }

    this.onClosePopup(event);
    dispatch(endChat(chatId));
  };

  onClosePopup = event => {
    event.preventDefault();
    this.setState({
      confirmPopup: false
    });
  };

  render() {
    const { children, confirmPosition, locked } = this.props;
    const childProps = children.props;
    const positionAt = confirmPosition || 'top';
    const positionMy = positionAt === 'top' ? 'bottom' : 'top';

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          locked,
          ref:         'button',
          onOpenPopup: this.onOpenPopup
        })}

        <Simple
          isOpen={this.state.confirmPopup}
          positionTarget={this.refs.button}
          positionAt={`right ${positionAt}`}
          positionMy={`right ${positionMy}`}
          zIndex={1000}
        >
          <ClickOut
            onClickOut={this.onClosePopup}
            context={[parent.document, window.widgetFrame.document]}
          >
            <EndChatConfirmPopup
              positionAt={positionAt}
              locked={locked}
              onConfirm={this.onEndChat}
              onCancel={this.onClosePopup}
            />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
