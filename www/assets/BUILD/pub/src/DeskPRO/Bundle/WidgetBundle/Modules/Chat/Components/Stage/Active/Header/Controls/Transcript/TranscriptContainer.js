import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptPopup } from './TranscriptPopup';
import { TranscriptForm } from './TranscriptForm';
import { TranscriptSent } from './TranscriptSent';
import { toggleSendTranscript, sendTranscriptInfo } from '../../../../../../Actions/chatActions';
import {
  chatIdSelector,
  authorEmailSelector,
  authorNameSelector,
  transcriptCheckedSelector,
  transcriptSentSelector,
  disabledPollingSelector
} from '../../../../../../Selectors/chat';

@connect(state => ({
  chatId:      chatIdSelector(state),
  authorName:  authorNameSelector(state),
  authorEmail: authorEmailSelector(state),
  checked:     transcriptCheckedSelector(state),
  sent:        transcriptSentSelector(state),
  disabled:    disabledPollingSelector(state)
}))
export class TranscriptContainer extends React.Component {

  static propTypes = {
    dispatch:    PropTypes.func,
    chatId:      PropTypes.string,
    authorName:  PropTypes.string,
    authorEmail: PropTypes.string,
    checked:     PropTypes.bool,
    disabled:    PropTypes.bool,
    sent:        PropTypes.bool,
    children:    PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened: false
    };
  }

  onClick = (event) => {
    event.preventDefault();
    const { dispatch, chatId, authorEmail, checked, sent } = this.props;

    if (authorEmail && checked && !sent) {
      dispatch(toggleSendTranscript(chatId, false));
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
        dispatch(toggleSendTranscript(chatId, true));
      }

      return null;
    }

    // update user info
    // return promise to get validation errors
    const promise = dispatch(sendTranscriptInfo(chatId, { name, email }));
    promise.then(() => this.onCloseForm());

    return promise;
  };

  render() {
    const { authorName, authorEmail, checked, disabled, sent, children } = this.props;
    const childProps = children.props;

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          disabled,
          ref:     (c) => { this.button = c; },
          active:  checked,
          onClick: this.onClick
        })}

        <Simple
          isOpen={this.state.formOpened}
          positionTarget={this.button}
          positionAt="center+40 bottom"
          positionMy="center top"
          zIndex={1000}
        >
          <ClickOut
            onClickOut={this.onCloseForm}
            context={[parent.document, window.widgetFrame.document]}
            additionalNodes={['.dpdesignportal-button']}
          >
            <TranscriptPopup onClose={this.onCloseForm}>
              {sent
                ? <TranscriptSent email={authorEmail} />
                :
                <TranscriptForm
                  name={authorName}
                  email={authorEmail}
                  onSubmit={this.onSubmit}
                />
              }
            </TranscriptPopup>
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
