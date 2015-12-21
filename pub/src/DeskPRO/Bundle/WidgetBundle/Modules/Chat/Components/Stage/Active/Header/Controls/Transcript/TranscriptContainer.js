import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptPopup } from './TranscriptPopup';
import { TranscriptForm } from './TranscriptForm';
import { TranscriptSent } from './TranscriptSent';
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
    const { dispatch, authorEmail, checked, sent } = this.props;

    if (authorEmail && checked && !sent) {
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
    // don't allow to close popup if no changes and empty email
    if (authorEmail && name === authorName && email === authorEmail) {
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
          onClick: this.onClick
        })}

        <Simple isOpen={this.state.formOpened}
                positionTarget={this.refs.button}
                positionAt="center+40 bottom"
                positionMy="center top"
                zIndex={1000}>

          <ClickOut onClickOut={this.onCloseForm}
                    context={[parent.document, window.widgetFrame.document]}
                    additionalNodes={['.dpdesignportal-button']}>

            <TranscriptPopup onClose={this.onCloseForm}>
              {sent
                ? <TranscriptSent email={authorEmail} />
                : <TranscriptForm name={authorName}
                                  email={authorEmail}
                                  onSubmit={this.onSubmit} />
              }
            </TranscriptPopup>
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
