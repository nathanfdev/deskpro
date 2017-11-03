import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import TransferList from './TransferList/TransferList';
import AddList from './AddList/AddList';

class VoiceControls extends React.Component {

  static propTypes = {
    status: PropTypes.string
  };

  static defaultProps = {
    onRedial:  () => {},
    onHold:    () => {},
    onMute:    () => {},
    onEndCall: () => {}
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
    onRedial: PropTypes.func
  };

  onRedial = (event) => {
    event.preventDefault();
    this.props.onRedial();
  };

  render() {
    return (
      <div className="voice-controls busy">
        <Title>
          Busy
        </Title>
        <Button className="green redial-button" onClick={this.onRedial}>
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
    onHold:       PropTypes.func,
    onMute:       PropTypes.func,
    onEndCall:    PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      transferMenuOpened: false,
      addMenuOpened:      false
    };
  }

  onHold = (event) => {
    event.preventDefault();
    this.props.onHold();
  };

  onMute = (event) => {
    event.preventDefault();
    this.props.onMute();
  };

  onClickTransfer = (event) => {
    event.preventDefault();
    this.setState({
      transferMenuOpened: true
    });
  };

  onClickAdd = (event) => {
    event.preventDefault();
    this.setState({
      addMenuOpened: true
    });
  };

  onEndCall = (event) => {
    event.preventDefault();
    this.props.onEndCall();
  };

  onCloseTransferMenu = () => {
    this.setState({
      transferMenuOpened: false
    });
  };

  onCloseAddMenu = () => {
    this.setState({
      addMenuOpened: false
    });
  };

  render() {
    const { hold, mute, ended, onlineAgents } = this.props;
    const { transferMenuOpened, addMenuOpened } = this.state;
    const noAgents = !onlineAgents || !onlineAgents.size;

    return (
      <div className={classNames('voice-controls active', { hold, ended })}>
        <Title>
          Duration: <Timer paused={ended} />
        </Title>

        <span className="voice-controls-recording">
          <i className="fa fa-dot-circle-o" />
          Recording
        </span>

        <Button
          className={classNames('basic', { active: hold, disabled: ended })}
          onClick={this.onHold}
        >
          <i className="pause icon" />
          Hold
        </Button>
        <Button
          className={classNames('basic', { active: mute, disabled: hold || ended })}
          onClick={this.onMute}
        >
          <i className={classNames(mute ? 'mute' : 'unmute', 'icon')} />
          Mute
        </Button>
        <Button
          ref={(c) => { this.transferButton = c; }}
          className={classNames('basic caret-button', { active: transferMenuOpened, disabled: ended || noAgents })}
          onClick={this.onClickTransfer}
        >
          <i className="share icon" />
          Transfer
        </Button>
        <Button
          ref={(c) => { this.addButton = c; }}
          className={classNames('basic', { active: addMenuOpened, disabled: ended || noAgents })}
          onClick={this.onClickAdd}
        >
          <i className="add icon" />
          Add
        </Button>
        <Button
          className={classNames('red', { disabled: ended })}
          onClick={this.onEndCall}
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
          <ClickOut onClickOut={this.onCloseTransferMenu}>
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
          <ClickOut onClickOut={this.onCloseAddMenu}>
            <AddList {...this.props} />
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
