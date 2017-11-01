import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { starsCountSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';
import { applyListParams } from '../../../Actions/listActions';

@connect(state => ({
  starsCount: starsCountSelector(state)
}))
export class StarsTabContainer extends Component {
  static propTypes = {
    dispatch:   PropTypes.func.isRequired,
    starsCount: PropTypes.object.isRequired
  };

  onStarClick = (id) => {
    this.props.dispatch(applyListParams({ star: id }));
  };

  render() {
    return <StarsTab {...this.props} onStarClick={this.onStarClick} />;
  }
}
