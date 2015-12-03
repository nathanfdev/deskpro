import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { starsCountSelector, isDoneSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

@connect(state => ({
  isDone: isDoneSelector(state),
  starsCount: starsCountSelector(state)
}))
export class StarsTabContainer extends Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <LoadIndicator loaded={this.props.isDone}>
        <StarsTab {...this.props} />
      </LoadIndicator>
    );
  }
}
