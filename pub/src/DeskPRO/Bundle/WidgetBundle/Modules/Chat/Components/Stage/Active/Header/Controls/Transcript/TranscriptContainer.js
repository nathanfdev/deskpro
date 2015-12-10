import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptForm } from './TranscriptForm';
import {
  disableSendTranscript,
  enableSendTranscript,
  sendTranscriptInfo
} from '../../../../../../Actions/chatActions';
import {
  chatIdSelector,
  authorEmailSelector,
  authorNameSelector,
  transcriptCheckedSelector,
  transcriptSentSelector
} from '../../../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  authorName: authorNameSelector(state),
  authorEmail: authorEmailSelector(state),
  checked: transcriptCheckedSelector(state),
  sent: transcriptSentSelector(state)
}))
export class TranscriptContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    authorName: PropTypes.string,
    authorEmail: PropTypes.string,
    checked: PropTypes.bool,
    sent: PropTypes.bool,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened: false
    };
  }

  onClick = event => {
    event.preventDefault();
    const { dispatch, authorEmail, checked } = this.props;

    if (authorEmail && checked) {
      dispatch(disableSendTranscript());
    } else {
      this.setState({
        formOpened: true
      });
    }
  };

  onCloseForm = () => {
    this.setState({
      formOpened: false
    });
  };

  onSubmit = ({ name, email }) => {
    const { dispatch, chatId, authorName, authorEmail } = this.props;

    // no changes
    if (name === authorName && email === authorEmail) {
      this.onCloseForm();

      if (email) {
        dispatch(enableSendTranscript());
      }

      return null;
    }

    // update user info
    const promise = dispatch(sendTranscriptInfo(chatId, {name, email}));
    if (promise) {
      promise.then(() => {
        this.onCloseForm();

        if (email) {
          dispatch(enableSendTranscript());
        }
      });
    }

    return promise;
  };

  render() {
    const { authorName, authorEmail, checked, sent, children } = this.props;
    const childProps = children.props;

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          ref: 'button',
          active: checked,
          disabled: sent,
          onClick: this.onClick
        })}

        <Simple isOpen={this.state.formOpened}
                positionTarget={this.refs.button}
                positionAt="center-18 bottom"
                positionMy="center top"
                zIndex={1000}>

          <ClickOut onClickOut={this.onCloseForm}
                    context={[parent.document, parent.window.widget_iframe.document]}
                    additionalNodes={['.dpdesignportal-button']}>

            <TranscriptForm name={authorName}
                            email={authorEmail}
                            onSubmit={this.onSubmit}
                            onClose={this.onCloseForm} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
