import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { starsCountSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';

@connect(state => ({
  starsCount: starsCountSelector(state)
}))

export class StarsTabContainer extends Component {
  static propTypes = {
    starsCount: PropTypes.object.isRequired
  };

  render() {
    return <StarsTab {...this.props} />;
  }
}
