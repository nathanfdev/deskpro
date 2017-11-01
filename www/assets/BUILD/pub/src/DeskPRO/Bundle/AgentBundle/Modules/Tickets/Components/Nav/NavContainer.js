import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';
import { isLoadedSelector } from '../../Selectors/nav';

@connect(state => ({
  isLoaded: isLoadedSelector(state)
}))
export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(initialLoad());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
