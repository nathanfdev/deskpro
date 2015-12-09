import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptForm } from './TranscriptForm';
import { authorEmailSelector, authorNameSelector, sendTranscriptSelector } from '../../../../../../Selectors/chat';
import { toggleSendTranscript } from '../../../../../../Actions/chatActions';

@connect(state => ({
  authorName: authorNameSelector(state),
  authorEmail: authorEmailSelector(state),
  enabled: sendTranscriptSelector(state)
}))
export class TranscriptContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    authorName: PropTypes.string,
    authorEmail: PropTypes.string,
    enabled: PropTypes.bool,
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

    const { dispatch, authorName, authorEmail } = this.props;
    if (authorName && authorEmail) {
      dispatch(toggleSendTranscript());
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

  onSubmit = data => {
    console.log('submit transcript form', data);
    this.onCloseForm();
  };

  render() {
    const { authorName, authorEmail, enabled, children } = this.props;
    const childProps = children.props;

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          ref: 'button',
          active: enabled,
          onClick: this.onClick
        })}

        <Simple isOpen={this.state.formOpened}
                positionTarget={this.refs.button}
                positionAt="center-18 bottom"
                positionMy="center top"
                zIndex={1000}>

          <ClickOut onClickOut={this.onCloseForm}
                    context={[parent.document, parent.window.widget_iframe.document]}>

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
