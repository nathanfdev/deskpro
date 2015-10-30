import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { filterSetsSelector, filterSetsCountSelector, filterNamesSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';

@connect(state => ({
  filterSets: filterSetsSelector(state),
  filterSetsCount: filterSetsCountSelector(state)
}))
export class FiltersTabContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterSets: PropTypes.object.isRequired,
    filterSetsCount: PropTypes.object.isRequired
  };

  render() {
    return (
      <FiltersTab {...this.props} />
    );
  }
}
