import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { filterSetsSelector, filterSetsCountSelector, isDoneSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';

@connect(state => ({
  isDone: isDoneSelector('filterSetsCount')(state) && isDoneSelector('filterSets')(state),
  filterSetsCount: filterSetsCountSelector(state),
  filterSets: filterSetsSelector(state)
}))
export class FiltersTabContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterSets: PropTypes.object.isRequired,
    filterSetsCount: PropTypes.object.isRequired
  };

  render() {
    return this.props.isDone ? <FiltersTab {...this.props} /> : <TabSpinner />;
  }
}
