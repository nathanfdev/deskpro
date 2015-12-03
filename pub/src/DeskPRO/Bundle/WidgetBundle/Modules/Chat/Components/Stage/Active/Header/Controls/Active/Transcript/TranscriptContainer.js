import React, { PropTypes } from 'react';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TranscriptForm } from './TranscriptForm';

export class TranscriptContainer extends React.Component {

  static propTypes = {
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

  render() {
    const { children } = this.props;
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

            <TranscriptForm />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
