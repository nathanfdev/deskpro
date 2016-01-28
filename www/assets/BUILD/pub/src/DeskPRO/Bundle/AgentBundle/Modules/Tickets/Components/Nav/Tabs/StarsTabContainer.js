import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { starsCountSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';

@connect(state => ({
  starsCount: starsCountSelector(state)
}))
export class StarsTabContainer extends Component {
  render() {
    return <StarsTab {...this.props} />;
  }
}
