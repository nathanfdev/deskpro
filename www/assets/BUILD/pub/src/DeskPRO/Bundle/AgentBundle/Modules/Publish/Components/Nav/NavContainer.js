import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { initialLoad, setMine } from '../../Actions/publishNavActions';
import { Nav } from './Nav';

@connect(state => ({
  isLoaded:  state.Publish.nav.getIn(['async', 'done']),
  articles:  state.Publish.nav.get('articles'),
  news:      state.Publish.nav.get('news'),
  downloads: state.Publish.nav.get('downloads'),
  todo:      state.Publish.nav.get('todo')
}))
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(initialLoad());
  };

  setMine = isMine => {
    this.props.dispatch(setMine(isMine));
  };

  render() {
    return <Nav {...this.props} setMine={this.setMine} />;
  }
}
