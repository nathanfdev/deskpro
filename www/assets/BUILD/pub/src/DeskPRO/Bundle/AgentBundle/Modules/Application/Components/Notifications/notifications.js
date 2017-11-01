import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { destroyNotification } from '../../Actions/notificationActions';
import { notificationsSelector } from  '../../Selectors/notifications';

@connect(state => ({
  notifications: notificationsSelector(state)
}))
export class NotificationsContainer extends React.Component {
  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    notifications: PropTypes.object.isRequired
  };

  render() {
    const props = {
      notifications: this.props.notifications,
      destroyNotification: (id) => () => this.props.dispatch(destroyNotification(id))
    };

    return <Notifications {...props} />;
  }
}

export class Notifications extends React.Component {
  static propTypes = {
    destroyNotification: PropTypes.func.isRequired,
    notifications:       PropTypes.object.isRequired
  };

  renderNotification(props) {
    const { destroyNotification } = this.props;

    switch (props.type) {
      case 'info':
        return <InfoNotification {...props} key={props.id} destroy={destroyNotification(props.id)} />;
      case 'error':
        return <ErrorNotification {...props} key={props.id} destroy={destroyNotification(props.id)} />;
      case 'delayed':
        return <DelayedActionNotification {...props} key={props.id} destroy={destroyNotification(props.id)} />;
      case 'undoable':
        return <UndoableActionNotification {...props} key={props.id} destroy={destroyNotification(props.id)} />;
      default:
        throw new Error(`Unknown notifications type: ${props.type}`);
    }
  }

  render() {
    return (
      <div>
        {this.props.notifications.map((notification, i) => this.renderNotification({ ...notification, num: i + 1 }))}
      </div>
    );
  }
}

class BaseNotification extends React.Component {
  static propTypes = {
    destroy: PropTypes.func.isRequired,
    alive:   PropTypes.number,
    num:     PropTypes.number.isRequired,
    title:   PropTypes.string.isRequired,
    text:    PropTypes.string
  };

  static defaultAlive = 9;

  constructor(props) {
    super(props);
    this.state = {
      hide: false
    };
  }

  componentDidMount() {
    this.letGoTimeout = setTimeout(this.letGo, (this.props.alive || BaseNotification.defaultAlive) * 1000);
  }

  componentWillUnmount() {
    clearTimeout(this.letGoTimeout);
  }

  onClick = () => {
    this.letGo();
  };

  getStyle() {
    return { marginBottom: `${75 * (this.props.num - 1)}px` };
  }

  letGo = () => {
    this.setState({ hide: true });
    setTimeout(this.props.destroy, 500);
  };

  renderIcon() {
    throw new Error('Children classes must re-declare renderIcon()');
  }

  renderOther() {
    return null;
  }

  render() {
    const { title, text } = this.props;
    const className = `dpwd--notication--growl${this.state.hide ? ' hide' : ''}`;

    return (
      <div className={className} style={this.getStyle()} onClick={this.onClick}>
        <span className="dpwd--notication--growl-icon">{this.renderIcon()}</span>
        <h1>{title}</h1>
        <p>{text}</p>

        {this.renderOther()}
      </div>
    );
  }

}

class BaseCountdownNotification extends BaseNotification {
  constructor(props) {
    super(props);
    this.state = {
      hide:    false,
      counter: props.alive || BaseNotification.defaultAlive
    };

    this.interval = null;
  }

  renderIcon() {
    return this.state.counter;
  }

  setCountdownInterval() {
    this.interval = setInterval(
      () => this.setState({ counter: this.state.counter > 1 ? this.state.counter - 1 : '' }),
      1000
    );
  }

  clearCountdownInterval() {
    clearInterval(this.interval);
  }

  componentDidMount() {
    super.componentDidMount();
    this.setCountdownInterval();
  }

  componentWillUnmount() {
    super.componentWillUnmount();
    this.clearCountdownInterval();
  }
}

class DelayedActionNotification extends BaseCountdownNotification {
  static propTypes = {
    commit: PropTypes.func.isRequired,
    undo:   PropTypes.func
  };

  constructor(props) {
    super(props);
    this.commited = false;
  }

  undo() {
    this.props.undo && this.props.undo();
  }

  onClick = () => {
    if (!this.commited) {
      this.props.commit();
      this.commited = true;
    }
    this.letGo();
  };

  onUndoClick = (e) => {
    e.stopPropagation();
    this.undo();
    this.letGo();
  };

  renderOther() {
    return (
      <div className="dpwd--notication--growl-undo-button">
        <a href="#" onClick={this.onUndoClick}><i className="fa fa-undo" /> Undo</a>
      </div>
    );
  }

  componentDidMount() {
    this.setCountdownInterval();

    // Perform commit() before destroying a DelayedActionNotification
    const onDestroy       = () => {
      if (!this.commited) {
        this.props.commit();
        this.commited = true;
      }
      this.letGo();
    };
    this.onDestroyTimeout = setTimeout(onDestroy, (this.props.alive || BaseNotification.defaultAlive) * 1000);
  }

  componentWillUnmount() {
    super.componentWillUnmount();
    clearTimeout(this.onDestroyTimeout);
  }
}

class UndoableActionNotification extends BaseCountdownNotification {
  static propTypes = {
    undo: PropTypes.func.isRequired
  };

  renderOther() {
    return (
      <div className="dpwd--notication--growl-undo-button">
        <a href="#" onClick={e => { e.preventDefault(); this.props.undo(); }}><i className="fa fa-undo" /> Undo</a>
      </div>
    );
  }
}

class InfoNotification extends BaseNotification {
  renderIcon() {
    return (
      <i className="fa fa-info" />
    );
  }
}

class ErrorNotification extends BaseNotification {
  renderIcon() {
    return (
      <i className="fa fa-exclamation-triangle" />
    );
  }
}
