import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';
import { initialLoad, unload } from '../../Actions/navActions';

@connect(state => ({}))
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return (
      <Nav {...this.props} />
    );
  }

  componentDidMount() {
    this.props.dispatch(initialLoad());
  }

  componentWillUnmount() {
    this.props.dispatch(unload());
  }
}
