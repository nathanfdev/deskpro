import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as legacyActions from '../Actions/legacyActions';

@connect()
export class LegacyLink extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    route: PropTypes.string.isRequired
  };

  onClick = (ev) => {
    ev.preventDefault();
    this.props.dispatch(legacyActions.loadRoute(this.props.route));
  }

  render() {
    return (
      <span className="legacy-route legacy-route-link" onClick={this.onClick}>
        {this.props.children}
      </span>
    )
  }
}

@connect()
export class LegacyLinkBlock extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    route: PropTypes.string.isRequired
  };

  onClick = (ev) => {
    ev.preventDefault();
    this.props.dispatch(legacyActions.loadRoute(this.props.route));
  }

  render() {
    return (
      <div className="legacy-route legacy-route-block" onClick={this.onClick}>
        {this.props.children}
      </div>
    )
  }
}
