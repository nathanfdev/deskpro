import React, { PropTypes } from 'react';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import CallFrom from './CallFrom';
import CallTarget from './CallTarget';

class IncomingCall extends React.Component {

  static propTypes = {
    me:          PropTypes.object,
    reservation: PropTypes.object,
    onAccept:    PropTypes.func,
    onDecline:   PropTypes.func,
    onHangup:    PropTypes.func
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

  renderAcceptButtons() {
    return (
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
    );
  }

  renderHangupButton() {
    const { onHangup } = this.props;

    return (
      <div className="buttons">
        <Button className="red" onClick={onHangup}>
          Hangup
        </Button>
      </div>
    );
  }

  render() {
    const { me, reservation } = this.props;
    const progress = reservation.reservationStatus === 'accepted';

    return (
      <div className="incoming-call">
        <CallFrom reservation={reservation} />
        <CallTarget target={{ type: 'agent', agent: me }} />

        {progress ? this.renderHangupButton() : this.renderAcceptButtons()}
      </div>
    );
  }
}

export default IncomingCall;
