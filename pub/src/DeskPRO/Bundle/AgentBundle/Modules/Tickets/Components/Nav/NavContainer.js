import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';

@connect()
export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return <Nav />;
  }

  componentDidMount() {
    this.props.dispatch(initialLoad());
  }
}
