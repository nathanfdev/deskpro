import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';

@connect(state => ({}))
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.props.dispatch(initialLoad());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
