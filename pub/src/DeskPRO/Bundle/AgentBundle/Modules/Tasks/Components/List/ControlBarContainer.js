import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import {
  currentViewModeSelector,
  currentSortSelector,
  currentOrderSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  kanbanVisibleFieldsSelector
} from '../../Selectors/tasks';
import {
  toggleCardFieldVisibility,
  toggleTableFieldVisibility,
  toggleKanbanFieldVisibility,
  toggleCalendarFieldVisibility
} from '../../Actions/tasksActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state),
  sort: currentSortSelector(state),
  order: currentOrderSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selectedCount: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    viewMode: PropTypes.string.isRequired,
    cardVisibleFields: PropTypes.object.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    kanbanVisibleFields: PropTypes.object.isRequired,
    calendarVisibleFields: PropTypes.object.isRequired
  };

  render() {
    const config = {
      sorting: {
        options: {
          list: {label: 'List', icon: 'list'},
          project: {label: 'Project', icon: 'briefcase'},
          date_due: {label: 'Due Date', icon: 'calendar'},
          date_done: {label: 'Done Date', icon: 'calendar'},
          date_created: {label: 'Created Date', icon: 'calendar'},
          assignee: {label: 'Assignee', icon: 'user'}
        },

        sort: this.props.sort,
        sortAction: value => updateRoutingState('list', 'sort', value),

        order: this.props.order,
        orderAction: value => updateRoutingState('list', 'order', value)
      },
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon: 'list',
            configurableFields: {
              id: 'ID',
              urgency: 'Urgency',
              person: 'Person',
              date_created: 'Date created',
              labels: 'Labels'
            },
            visibleFields: this.props.cardVisibleFields,
            toggleFieldVisibility: toggleCardFieldVisibility
          },
          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon: 'table',
            configurableFields: {
              subject: 'Subject',
              status: 'Status',
              date_created: 'Date created',
              labels: 'Labels'
            },
            visibleFields: this.props.tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility
          },
          [constants.VIEW_MODE_KANBAN]: {
            label: 'Kanban View',
            icon: 'sticky-note-o',
            configurableFields: {
              subject: 'Subject',
              status: 'Status'
            },
            visibleFields: this.props.kanbanVisibleFields,
            toggleFieldVisibility: toggleKanbanFieldVisibility
          },
          [constants.VIEW_MODE_CALENDAR]: {
            label: 'Calendar View',
            icon: 'calendar',
            configurableFields: {
              subject: 'Subject',
              status: 'Status'
            },
            visibleFields: this.props.calendarVisibleFields,
            toggleFieldVisibility: toggleCalendarFieldVisibility
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: value => updateRoutingState('list', 'view', value)
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
