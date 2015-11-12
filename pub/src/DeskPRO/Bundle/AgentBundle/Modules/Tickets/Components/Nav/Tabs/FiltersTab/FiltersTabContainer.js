import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { filterSetsSelector, filterSetsCountSelector, isDoneSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';

@connect(state => ({
  isDone: isDoneSelector(state),
  filterSetsCount: filterSetsCountSelector(state),
  filterSets: filterSetsSelector(state)
}))
export class FiltersTabContainer extends Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <TabSpinner loaded={this.props.isDone}>
        <FiltersTab {...this.props} />
      </TabSpinner>
    );
  }
}
