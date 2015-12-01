import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { EndChatConfirm } from './EndChatConfirm';
import { endChat } from '../../../../Actions/chatActions';
import { chatIdSelector } from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state)
}))
export class EndChatContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    confirmPosition: PropTypes.string,
    children: PropTypes.node
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
    const { chatId, dispatch } = this.props;

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
    const { children, confirmPosition } = this.props;
    const childProps = children.props;
    const positionAt = confirmPosition || 'top';
    const positionMy = positionAt === 'top' ? 'bottom' : 'top';

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          ref: 'button',
          onOpenPopup: this.onOpenPopup
        })}

        <Simple isOpen={this.state.confirmPopup}
                positionTarget={this.refs.button}
                positionAt={`right ${positionAt}`}
                positionMy={`right ${positionMy}`}
                zIndex={100}>

          <ClickOut onClickOut={this.onClosePopup}
                    context={[parent.document, parent.window.widget_iframe.document]}>

            <EndChatConfirm positionAt={positionAt}
                            onConfirm={this.onEndChat}
                            onCancel={this.onClosePopup} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
