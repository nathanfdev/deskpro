import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import TransferList from './TransferList/TransferList';
import AddList from './AddList/AddList';
import DialGrid from '../Common/DialGrid';

class VoiceControls extends React.Component {

  static propTypes = {
    status: PropTypes.string
  };

  static defaultProps = {
    redial:     () => {},
    toggleHold: () => {},
    toggleMute: () => {},
    endCall:    () => {},
    sendDigits: () => {}
  };

  render() {
    const { status } = this.props;

    switch (status) {
      case 'dialing':
        return <Connecting title="Dialing ..." />;
      case 'connecting':
        return <Connecting title="Connecting ..." />;
      case 'ringing':
        return <Connecting title="Ringing ..." />;
      case 'connected':
        return <Connecting title="Connected" className="active" />;
      case 'busy':
        return <Busy {...this.props} />;
      case 'active':
      case 'closed':
        return (
          <Active
            {...this.props}
            ended={status === 'closed'}
          />
        );
      default:
        return null;
    }
  }
}

class Connecting extends React.Component {

  static propTypes = {
    title:     PropTypes.string,
    className: PropTypes.string
  };

  render() {
    const { title, className } = this.props;

    return (
      <div className={classNames('voice-controls', className)}>
        <Title>
          {title}
        </Title>
      </div>
    );
  }
}

class Busy extends React.Component {

  static propTypes = {
    redial: PropTypes.func
  };

  redial = (event) => {
    event.preventDefault();
    this.props.redial();
  };

  render() {
    return (
      <div className="voice-controls busy">
        <Title>
          Busy
        </Title>
        <Button className="green redial-button" onClick={this.redial}>
          <i className="call icon" />
          Redial
        </Button>
      </div>
    );
  }
}

class Active extends React.Component {

  static propTypes = {
    mute:         PropTypes.bool,
    hold:         PropTypes.bool,
    ended:        PropTypes.bool,
    onlineAgents: PropTypes.object,
    toggleHold:   PropTypes.func,
    toggleMute:   PropTypes.func,
    endCall:      PropTypes.func,
    sendDigits:   PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      transferMenuOpened: false,
      addMenuOpened:      false,
      dialpadOpened:      false
    };
  }

  toggleHold = (event) => {
    event.preventDefault();
    this.props.toggleHold();
  };

  toggleMute = (event) => {
    event.preventDefault();
    this.props.toggleMute();
  };

  openTransferMenu = (event) => {
    event.preventDefault();
    this.setState({
      transferMenuOpened: true
    });
  };

  openAddMenu = (event) => {
    event.preventDefault();
    this.setState({
      addMenuOpened: true
    });
  };

  endCall = (event) => {
    event.preventDefault();
    this.props.endCall();
  };

  closeTransferMenu = () => {
    this.setState({
      transferMenuOpened: false
    });
  };

  closeAddMenu = () => {
    this.setState({
      addMenuOpened: false
    });
  };

  openDialpad = (event) => {
    event.preventDefault();
    this.setState({
      dialpadOpened: true
    });
  };

  closeDialpad = () => {
    this.setState({
      dialpadOpened: false
    });
  };

  render() {
    const { hold, mute, ended, onlineAgents, sendDigits } = this.props;
    const { transferMenuOpened, addMenuOpened, dialpadOpened } = this.state;
    const noAgents = !onlineAgents || !onlineAgents.size;

    return (
      <div className={classNames('voice-controls active', { hold, ended })}>
        <Title>
          Duration: <Timer paused={ended} />
        </Title>

        <span className="voice-controls-recording">
          <i className="far fa-dot-circle" />
          Recording
        </span>

        <Button
          ref={(c) => { this.dialpadButton = c; }}
          className={classNames('basic', { active: dialpadOpened, disabled: ended })}
          onClick={this.openDialpad}
        >
          <i className="grid layout icon" />
          Dialpad
        </Button>
        <Button
          className={classNames('basic', { active: hold, disabled: ended })}
          onClick={this.toggleHold}
        >
          <i className="pause icon" />
          Hold
        </Button>
        <Button
          className={classNames('basic', { active: mute, disabled: hold || ended })}
          onClick={this.toggleMute}
        >
          <i className={classNames(mute ? 'mute' : 'unmute', 'icon')} />
          Mute
        </Button>
        <Button
          ref={(c) => { this.transferButton = c; }}
          className={classNames('basic caret-button', { active: transferMenuOpened, disabled: ended || noAgents })}
          onClick={this.openTransferMenu}
        >
          <i className="share icon" />
          Transfer
        </Button>
        <Button
          ref={(c) => { this.addButton = c; }}
          className={classNames('basic', { active: addMenuOpened, disabled: ended || noAgents })}
          onClick={this.openAddMenu}
        >
          <i className="add icon" />
          Add
        </Button>
        <Button
          className={classNames('red', { disabled: ended })}
          onClick={this.endCall}
        >
          End call
        </Button>

        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={transferMenuOpened}
          positionTarget={this.transferButton}
          zIndex={1000}
        >
          <ClickOut onClickOut={this.closeTransferMenu}>
            <TransferList {...this.props} />
          </ClickOut>
        </Detached>
        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={addMenuOpened}
          positionTarget={this.addButton}
          zIndex={1000}
        >
          <ClickOut onClickOut={this.closeAddMenu}>
            <AddList {...this.props} />
          </ClickOut>
        </Detached>
        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={dialpadOpened}
          positionTarget={this.dialpadButton}
          zIndex={1000}
        >
          <ClickOut onClickOut={this.closeDialpad}>
            <div className="voice-ticket-dialpad">
              <DialGrid onClick={sendDigits} />
            </div>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}

class Title extends React.Component {

  static propTypes = {
    children: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ])
  };

  render() {
    const { children } = this.props;

    return (
      <span className="voice-controls-title">
        <i className="fa fa-phone" /> {children}
      </span>
    );
  }
}

export default VoiceControls;
