import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { filterSetsSelector, filterSetsCountSelector, filterNamesSelector } from '../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';
import { startFilterEditing } from '../../../Actions/navActions';

@connect(state => ({
  filterSets: filterSetsSelector(state),
  filterSetsCount: filterSetsCountSelector(state),
  filterNames: filterNamesSelector(state)
}))
export class FiltersTabContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterSets: PropTypes.object.isRequired,
    filterSetsCount: PropTypes.object.isRequired,
    filterNames: PropTypes.object.isRequired
  };

  render() {
    const props = Object.assign({onItemControlClick: this.startFilterEditing}, this.props);

    return (
      <FiltersTab {...props} />
    );
  }

  startFilterEditing = filterId => () => this.props.dispatch(startFilterEditing(filterId));
}
