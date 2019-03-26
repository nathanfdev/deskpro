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
    status: PropTypes.string,
    baseId: PropTypes.string
  };

  static defaultProps = {
    redial:     () => {},
    toggleHold: () => {},
    toggleMute: () => {},
    endCall:    () => {},
    sendDigits: () => {}
  };

  componentDidMount = () => {
    this.updateWindowDimensions();
    window.addEventListener('resize', this.updateWindowDimensions);
  };
  componentDidUpdate = () => {
    this.updateWindowDimensions();
  };

  componentWillUnmount = () => {
    const baseId = this.props.baseId;
    const content = window.document.getElementById(`${baseId}_page_header`);
    if (content) {
      content.style.paddingTop = '10px';
    }
    window.removeEventListener('resize', this.updateWindowDimensions);
  };

  updateWindowDimensions = () => {
    if (!this.ticking) {
      window.requestAnimationFrame(() => {
        const baseId = this.props.baseId;
        const content = window.document.getElementById(`${baseId}_page_header`);
        if (content && this.div) {
          content.style.paddingTop = `${this.div.clientHeight + 20}px`;
        }
        this.ticking = false;
      });
    }
    this.ticking = true;
  };

  render() {
    const { status } = this.props;

    switch (status) {
      case 'dialing':
        return (<Connecting
          divRef={(c) => { this.div = c; }}
          title="Dialing ..."
        />);
      case 'connecting':
        return (<Connecting
          divRef={(c) => { this.div = c; }}
          title="Connecting ..."
        />);
      case 'ringing':
        return (<Connecting
          divRef={(c) => { this.div = c; }}
          title="Ringing ..."
        />);
      case 'connected':
        return (<Connecting
          divRef={(c) => { this.div = c; }}
          title="Connected" className="active"
        />);
      case 'busy':
        return (<Busy
          divRef={(c) => { this.div = c; }}
          {...this.props}
        />);
      case 'active':
      case 'closed':
        return (
          <Active
            divRef={(c) => { this.div = c; }}
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
    className: PropTypes.string,
    divRef:    PropTypes.func
  };

  render() {
    const { title, className, divRef } = this.props;

    return (
      <div
        className={classNames('voice-controls', className)}
        ref={divRef}
      >
        <Title>
          {title}
        </Title>
      </div>
    );
  }
}

class Busy extends React.Component {

  static propTypes = {
    divRef: PropTypes.func,
    redial: PropTypes.func
  };

  redial = (event) => {
    event.preventDefault();
    this.props.redial();
  };

  render() {
    return (
      <div
        className="voice-controls busy"
        ref={this.props.divRef}
      >
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
    sendDigits:   PropTypes.func,
    divRef:       PropTypes.func,
    participants: PropTypes.array
  };

  constructor(props) {
    super(props);
    this.state = {
      transferMenuOpened: false,
      addMenuOpened:      false,
      dialpadOpened:      false,
      updatingHold:       false,
    };
  }

  toggleHold = (event) => {
    event.preventDefault();
    this.setState({
      updatingHold: true
    });

    const promise = this.props.toggleHold();
    if (promise) {
      promise.success(() => {
        this.setState({
          updatingHold: false,
        });
      });
    }
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
    const { hold, mute, ended, onlineAgents, participants, sendDigits, divRef } = this.props;
    const { transferMenuOpened, addMenuOpened, dialpadOpened, updatingHold } = this.state;
    const noAgents = !onlineAgents || !onlineAgents.size;

    return (
      <div
        ref={divRef}
        className={classNames('voice-controls active', { hold, ended })}
      >
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
          className={classNames('basic', { active: hold, disabled: ended, loading: updatingHold })}
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
          {participants.length >= 2 ? 'Hang up' : 'End call'}
        </Button>

        <Detached
          positionMy="right top"
          positionAt="right bottom"
          isOpen={transferMenuOpened}
          positionTarget={this.transferButton}
          zIndex={1000}
        >
          <ClickOut onClickOut={this.closeTransferMenu}>
            <TransferList {...this.props} closeMenu={this.closeTransferMenu} />
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
