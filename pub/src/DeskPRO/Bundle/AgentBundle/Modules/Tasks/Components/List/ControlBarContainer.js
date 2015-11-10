import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { currentViewModeSelector } from '../../Selectors/tasks';
import { changeView } from '../../Actions/tasksActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selectedCount: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    tableVisibleFields: PropTypes.array.isRequired,
    cardVisibleFields: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const config = {
      sorting: {
        options: [
          {field: 'list', label: 'List', icon: 'list' },
          {field: 'project', label: 'Project', icon: 'briefcase' },
          {field: 'date_due', label: 'Due Date', icon: 'calendar' },
          {field: 'date_done', label: 'Done Date', icon: 'calendar' },
          {field: 'date_created', label: 'Created Date', icon: 'calendar' },
          {field: 'assignee', label: 'Assignee', icon: 'user' }
        ],
        sort: 'list',
        order: 'order',
        sortAction: () => {},
        orderAction: () => {}
      },
      view: {
        options: [
          {field: constants.VIEW_MODE_CARD, label: 'Card View', icon: 'list'},
          {field: constants.VIEW_MODE_TABLE, label: 'Table View', icon: 'table'},
          {field: constants.VIEW_MODE_KANBAN, label: 'Kanban View', icon: 'sticky-note-o'},
          {field: constants.VIEW_MODE_CALENDAR, label: 'Calendar View', icon: 'calendar'}
        ],
        viewMode: this.props.viewMode,
        viewModeAction: changeView,
        tableToggleFieldVisibility: () => {},
        cardToggleFieldVisibility: () => {}
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
