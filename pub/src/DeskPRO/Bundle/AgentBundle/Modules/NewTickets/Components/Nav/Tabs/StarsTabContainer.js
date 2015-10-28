import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { starsCountSelector, starNamesSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';

@connect(state => ({
  starsCount: starsCountSelector(state),
  starNames: starNamesSelector(state)
}))
export class StarsTabContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    starsCount: PropTypes.object.isRequired,
    starNames: PropTypes.object.isRequired
  };

  render() {
    return this.props.starsCount && this.props.starNames ? <StarsTab {...this.props} /> : <div />;
  }
}
