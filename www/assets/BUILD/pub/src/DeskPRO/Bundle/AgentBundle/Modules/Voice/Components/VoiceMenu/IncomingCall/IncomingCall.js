import React, { PropTypes } from 'react';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import CallFrom from './CallFrom';
import CallTarget from './CallTarget';

class IncomingCall extends React.Component {

  static propTypes = {
    callFrom:   PropTypes.object,
    callTarget: PropTypes.object,
    onAccept:   PropTypes.func,
    onDecline:  PropTypes.func
  };

  static defaultProps = {
    onAccept:  () => {},
    onDecline: () => {}
  };

  onAccept = (event) => {
    event.preventDefault();
    this.props.onAccept();
  };

  onDecline = (event) => {
    event.preventDefault();
    this.props.onDecline();
  };

  render() {
    const { callFrom, callTarget } = this.props;

    return (
      <div className="incoming-call">
        <CallFrom callFrom={callFrom} />
        <CallTarget target={callTarget} />

        <div className="buttons">
          <Button
            className="green call-button"
            onClick={this.onAccept}
          >
            <i className="icon call" />
            Answer
            <span className="waiting-time">
              <Timer format="waiting_time" />
            </span>
          </Button>
          <a
            className="ignore-button"
            href="#ignore"
            onClick={this.onDecline}
          >
            <i className="fa fa-close" />
            Ignore
          </a>
        </div>
      </div>
    );
  }
}

export default IncomingCall;
