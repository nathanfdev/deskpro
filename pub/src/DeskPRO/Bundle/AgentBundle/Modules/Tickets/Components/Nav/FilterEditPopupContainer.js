import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { editedFilterSelector } from '../../Selectors/nav';
import { ListGroupingControl } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { applyFilterEditing, closeFilterEditing } from '../../Actions/navActions';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import Immutable from 'immutable';

@connect(state => ({
  filter: editedFilterSelector(state),
  grouping: filterSetGroupingsSettingsSelector(state)
}))
export class FilterEditPopupContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterId: PropTypes.number.isRequired,
    filter: PropTypes.object.isRequired,
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

  applyFilterEditing = (e) => {
    const options = e.target.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        this.props.dispatch(applyFilterEditing(options[i].value));
        break;
      }
    }
  };

  closeFilterEditing = () => this.props.dispatch(closeFilterEditing());

  render() {
    const { filter = Immutable.fromJS({}), grouping, attachTo, filterId } = this.props;
    const groupBy = grouping.get(String(filterId), '');

    return (
      <ListGroupingControl visible={filter.get('id') === filterId}
                           title={filter.get('title')}
                           options={FilterEditPopupContainer.groupingOptions}
                           onChange={this.applyFilterEditing}
                           close={this.closeFilterEditing}
                           selected={groupBy}
                           attachTo={attachTo}/>
    );
  }
}
