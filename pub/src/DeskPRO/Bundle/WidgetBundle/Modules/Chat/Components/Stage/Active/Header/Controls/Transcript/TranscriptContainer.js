import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptForm } from './TranscriptForm';
import { authorEmailSelector, authorNameSelector } from '../../../../../../Selectors/chat';

@connect(state => ({
  authorName: authorNameSelector(state),
  authorEmail: authorEmailSelector(state)
}))
export class TranscriptContainer extends React.Component {

  static propTypes = {
    authorName: PropTypes.string,
    authorEmail: PropTypes.string,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened: false
    };
  }

  onOpenForm = event => {
    event.preventDefault();
    this.setState({
      formOpened: true
    });
  };

  onCloseForm = event => {
    event.preventDefault();
    this.setState({
      formOpened: false
    });
  };

  onSubmit = data => {
    console.log('submit transcript form', data);
  };

  render() {
    const { authorName, authorEmail, children } = this.props;
    const childProps = children.props;

    return (
      <span>
        {React.cloneElement(children, {
          ...childProps,

          ref: 'button',
          onOpenForm: this.onOpenForm
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
