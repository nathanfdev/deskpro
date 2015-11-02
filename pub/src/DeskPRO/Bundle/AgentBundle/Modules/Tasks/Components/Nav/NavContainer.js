import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
