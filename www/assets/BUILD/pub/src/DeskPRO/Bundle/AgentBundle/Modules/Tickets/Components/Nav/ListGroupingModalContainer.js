import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ListGroupingModal } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import { editedFilterSelector } from '../../Selectors/nav';
import { applyFilterEditingActionFactory, closeFilterEditing } from '../../Actions/navActions';
import Immutable from 'immutable';

@connect(state => ({
  filter:   editedFilterSelector(state),
  grouping: filterSetGroupingsSettingsSelector(state)
}))
export class ListGroupingModalContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filter:   PropTypes.object,
    grouping: PropTypes.object.isRequired,
    filterId: PropTypes.number.isRequired,
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

  render() {
    const { filter = Immutable.fromJS({}), grouping, attachTo, filterId, dispatch } = this.props;

    let selected = '';
    let prefId;
    if (grouping.get(String(filterId))) {
      selected = grouping.get(String(filterId)).get('main_grouping');
      prefId   = grouping.get(String(filterId)).get('id');
    }

    const close = () => dispatch(closeFilterEditing());
    const apply = (groupBy) => {
      const action = applyFilterEditingActionFactory(prefId);
      dispatch(action(groupBy));
    };

    const visible = filter.get('id') === filterId;
    const options = ListGroupingModalContainer.groupingOptions;
    const title   = filter.get('title') || '-';

    const modal = {attachTo, visible, title, options, selected, apply, close};

    return <ListGroupingModal {...modal} />;
  }
}
