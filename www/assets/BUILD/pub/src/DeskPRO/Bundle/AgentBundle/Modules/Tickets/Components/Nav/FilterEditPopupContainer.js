import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListGroupingControlContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import { editedFilterSelector } from '../../Selectors/nav';
import { applyFilterEditing } from '../../Actions/navActions';
import Immutable from 'immutable';

@connect(state => ({
  filter:   editedFilterSelector(state),
  grouping: filterSetGroupingsSettingsSelector(state)
}))
export class FilterEditPopupContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterId: PropTypes.number.isRequired,
    filter:   PropTypes.object,
    grouping: PropTypes.object.isRequired,
    attachTo: PropTypes.any.isRequired
  };

  static groupingOptions = [
    { value: '', label: 'None' },
    { value: 'department', label: 'Department' },
    { value: 'organization', label: 'Organization' },
    { value: 'person', label: 'Person' },
    { value: 'language', label: 'Language' },
    { value: 'urgency', label: 'Urgency' },
    { value: 'agent', label: 'Agent' },
    { value: 'agent_team', label: 'Agent Team' },
    { value: 'waiting_time', label: 'Waiting Time' },
    { value: 'all_waiting_time', label: 'All Waiting Time' },
    { value: 'open_time', label: 'Open Time' }
  ];

  closeFilterEditing = () => this.props.dispatch(closeFilterEditing());

  render() {
    const { filter = Immutable.fromJS({}), grouping, attachTo, filterId } = this.props;
    let groupBy = '';
    if (grouping.get(String(filterId))) {
      groupBy = grouping.get(String(filterId)).get('main_grouping');
    }
    const content = filter.get('title') || 'none';

    return (
      <ListGroupingControlContainer
        visible={filter.get('id') === filterId}
        content={content}
        options={FilterEditPopupContainer.groupingOptions}
        changeListGrouping={applyFilterEditing}
        closeGroupingVisibility={this.closeFilterEditing}
        selected={groupBy}
        attachTo={attachTo}
      />
    );
  }
}
