import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { EndChatConfirm } from './EndChatConfirm';

@connect()
export class EndChatButtonContainer extends React.Component {

  static propTypes = {
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
    this.onClosePopup(event);
    console.log('onEndChat');
  };

  onClosePopup = event => {
    event.preventDefault();
    this.setState({
      confirmPopup: false
    });
  };

  render() {
    const { children } = this.props;
    const childProps = children.props;

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          ref: 'button',
          onOpenPopup: this.onOpenPopup
        })}

        <Simple isOpen={this.state.confirmPopup}
                positionTarget={this.refs.button}
                positionAt="right top"
                positionMy="right bottom">

          <ClickOut onClickOut={this.onClosePopup}
                    context={parent.document}>

            <EndChatConfirm onConfirm={this.onEndChat}
                            onCancel={this.onClosePopup} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
