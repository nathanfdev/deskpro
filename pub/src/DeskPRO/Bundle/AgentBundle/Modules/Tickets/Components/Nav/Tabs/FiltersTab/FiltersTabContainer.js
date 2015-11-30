import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { filterSetsSelector, filterSetsCountSelector, isDoneSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => ({
  isDone: isDoneSelector(state),
  filterSetsCount: filterSetsCountSelector(state),
  filterSets: filterSetsSelector(state),
  currentApp: state.Application.dpWindow.get('activeAppId')
}))
export class FiltersTabContainer extends Component {

  static propTypes = {
    currentApp: PropTypes.string.isRequired,
    isDone: PropTypes.bool.isRequired
  };

  render() {
    const { currentApp } = this.props;

    return (
      <TabSpinner loaded={this.props.isDone}
                  color={constants.APP_COLOURS[currentApp]}>
        <FiltersTab {...this.props} />
      </TabSpinner>
    );
  }
}
