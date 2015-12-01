import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { filterSetsCountSelector, isDoneSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

@connect(state => ({
  isDone: isDoneSelector(state),
  filterSetsCount: filterSetsCountSelector(state)
}))
export class FiltersTabContainer extends Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <LoadIndicator loaded={this.props.isDone}>
        <FiltersTab {...this.props} />
      </LoadIndicator>
    );
  }
}
