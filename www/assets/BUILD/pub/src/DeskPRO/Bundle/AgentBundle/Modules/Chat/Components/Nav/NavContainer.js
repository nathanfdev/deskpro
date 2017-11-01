import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { initialLoad, changeCountGrouping } from '../../Actions/navActions';
import { isLoadedSelector, countsSelector } from '../../Selectors/nav';
import { Nav } from './Nav';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  counts:   countsSelector(state)
}))
@pureRender
export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(initialLoad());
  };

  changeCountGroupingCallbackFactory = (countId) => (groupBy) => {
    this.props.dispatch(changeCountGrouping(countId, groupBy));
  };

  render() {
    return <Nav {...this.props} changeCountGroupingCallbackFactory={this.changeCountGroupingCallbackFactory} />;
  }
}
