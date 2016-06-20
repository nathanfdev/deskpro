import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import * as actions from '../../Actions/chatNavActions';
import { isLoadedSelector, myChatsSelector, allChatsSelector } from '../../Selectors/nav';
import { Nav } from './Nav';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  my:       myChatsSelector(state),
  all:      allChatsSelector(state)
}))
@pureRender
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount = () => {
    this.props.dispatch(actions.initialLoad());
  };

  componentWillUnmount = () => {
    this.props.dispatch(actions.unmount());
  };

  toggleGroupingVisibility = listName => e => {
    e.preventDefault();
    this.props.dispatch(actions.toggleListGroupingVisibility(listName));
  };

  render = () => {
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName);

    return (
      <Nav
        {...this.props}
        toggleGroupingVisibility={toggleGroupingVisibility}
      />
    );
  }
}
