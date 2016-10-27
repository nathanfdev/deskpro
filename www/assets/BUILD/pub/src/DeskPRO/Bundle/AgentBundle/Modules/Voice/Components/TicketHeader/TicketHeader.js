import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import TransferListContainer from './TransferList/TransferListContainer';
import AddListContainer from './AddList/AddListContainer';

class TicketHeader extends React.Component {

  static propTypes = {
    status:    PropTypes.string,
    hold:      PropTypes.bool,
    onRedial:  PropTypes.func,
    onHold:    PropTypes.func,
    onMute:    PropTypes.func,
    onEndCall: PropTypes.func
  };

  static defaultProps = {
    onRedial:  () => {},
    onHold:    () => {},
    onMute:    () => {},
    onEndCall: () => {}
  };

  render() {
    const { status, hold } = this.props;
    const { onRedial, onHold, onMute, onEndCall } = this.props;

    switch (status) {
      case 'dial':
        return <Connecting title="Dialling ..." />;
      case 'connect':
        return <Connecting title="Connecting ..." />;
      case 'ring':
        return <Connecting title="Ringing ..." />;
      case 'connected':
        return <Connecting title="Connected" className="active" />;
      case 'busy':
        return <Busy onRedial={onRedial} />;
      case 'active':
        return (
          <Active
            hold={hold}
            onHold={onHold}
            onMute={onMute}
            onEndCall={onEndCall}
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
      <div className={classNames('voice-ticket-header', className)}>
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
      <div className="voice-ticket-header busy">
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
    hold:      PropTypes.bool,
    onHold:    PropTypes.func,
    onMute:    PropTypes.func,
    onEndCall: PropTypes.func
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
    const { hold } = this.props;
    const { transferMenuOpened, addMenuOpened } = this.state;

    return (
      <div className={classNames('voice-ticket-header active', { hold })}>
        <Title>
          Duration: <Timer />
        </Title>

        <span className="voice-ticket-header-recording">
          <i className="fa fa-dot-circle-o" />
          Recording
        </span>

        <Button className={classNames('basic', { active: hold })} onClick={this.onHold}>
          <i className="pause icon" />
          Hold
        </Button>
        <Button className={classNames('basic', { disabled: hold })} onClick={this.onMute}>
          <i className="mute icon" />
          Mute
        </Button>
        <Button
          ref={(c) => { this.transferButton = c; }}
          className={classNames('basic caret-button', { active: transferMenuOpened })}
          onClick={this.onClickTransfer}
        >
          <i className="share icon" />
          Transfer
          <i className="caret down icon" />
        </Button>
        <Button
          ref={(c) => { this.addButton = c; }}
          className={classNames('basic', { active: addMenuOpened })}
          onClick={this.onClickAdd}
        >
          <i className="add user icon" />
          Add
        </Button>
        <Button className="red" onClick={this.onEndCall}>
          End call
        </Button>

        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={transferMenuOpened}
          positionTarget={this.transferButton}
        >
          <ClickOut onClickOut={this.onCloseTransferMenu}>
            <TransferListContainer {...this.props} />
          </ClickOut>
        </Detached>
        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={addMenuOpened}
          positionTarget={this.addButton}
        >
          <ClickOut onClickOut={this.onCloseAddMenu}>
            <AddListContainer {...this.props} />
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
      <span className="voice-ticket-header-title">
        <i className="fa fa-phone" /> {children}
      </span>
    );
  }
}

export default TicketHeader;
