import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';
import { initialLoad, unload } from '../../Actions/navActions';

@connect(state => ({
  currentApp: state.Application.dpWindow.get('activeAppId')
}))
export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired
  };

  render() {
    const { currentApp } = this.props;
    return (
      <Nav currentApp={currentApp}/>
    );
  }

  componentDidMount() {
    this.props.dispatch(initialLoad());
  }

  componentWillUnmount() {
    this.props.dispatch(unload());
  }
}
