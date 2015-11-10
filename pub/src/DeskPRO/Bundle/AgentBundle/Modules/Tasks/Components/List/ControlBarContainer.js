import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { currentViewModeSelector, currentSortSelector, currentOrderSelector } from '../../Selectors/tasks';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state),
  sort: currentSortSelector(state),
  order: currentOrderSelector(state)
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
        options: {
          list: {
            label: 'List',
            icon: 'list'
          },
          project: {
            label: 'Project',
            icon: 'briefcase'
          },
          date_due: {
            label: 'Due Date',
            icon: 'calendar'
          },
          date_done: {
            label: 'Done Date',
            icon: 'calendar'
          },
          date_created: {
            label: 'Created Date',
            icon: 'calendar'
          },
          assignee: {
            label: 'Assignee',
            icon: 'user'
          }
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
            visibleFields: [],
            toggleFieldVisibility: () => {}
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
            visibleFields: [],
            toggleFieldVisibility: () => {}
          },
          [constants.VIEW_MODE_KANBAN]: {
            label: 'Kanban View',
            icon: 'sticky-note-o'
          },
          [constants.VIEW_MODE_CALENDAR]: {
            label: 'Calendar View',
            icon: 'calendar'
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
