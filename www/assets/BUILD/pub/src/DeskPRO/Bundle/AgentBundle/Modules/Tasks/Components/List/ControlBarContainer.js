import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { applyFilters } from '../../Actions/listActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { loadAll, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

import {
  currentViewModeSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  listParamsFiltersSelector
} from '../../Selectors/list';
import {
  toggleCardFieldVisibility,
  toggleTableFieldVisibility,
  toggleKanbanFieldVisibility,
  toggleCalendarFieldVisibility,
} from '../../Actions/listActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state),
  currentParams: listParamsFiltersSelector(state),
  labels: allSelectorFactory('TaskLabel')(state)
}))
export class ControlBarContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    viewMode: PropTypes.string.isRequired,
    cardVisibleFields: PropTypes.object.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    kanbanVisibleFields: PropTypes.object.isRequired,
    calendarVisibleFields: PropTypes.object.isRequired,
    currentParams: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadAll('TaskLabel'));
  }

  render() {
    const { labels = [], currentParams, viewMode } = this.props;
    const { cardVisibleFields, tableVisibleFields, kanbanVisibleFields, calendarVisibleFields } = this.props;

    const config = {
      applyParams: applyFilters,
      currentParams: currentParams,
      sorting: {
        list: { label: 'List', icon: 'list' },
        project: { label: 'Project', icon: 'briefcase' },
        date_due: { label: 'Due Date', icon: 'calendar' },
        date_done: { label: 'Done Date', icon: 'calendar' },
        date_created: { label: 'Created Date', icon: 'calendar' },
        assignee: { label: 'Assignee', icon: 'user' }
      },
      filters: [
        {
          label: 'Date Created',
          type: 'date',
          fromParam: 'created_from',
          toParam: 'created_to'
        },
        {
          label: 'Date Due',
          type: 'date',
          fromParam: 'due_from',
          toParam: 'due_to'
        },
        {
          label: 'Date Done',
          type: 'date',
          fromParam: 'done_from',
          toParam: 'done_to'
        },
        {
          label: 'Status', type: 'select', param: 'done', multiple: false,
          options: [
            { value: 'done', label: 'Done' },
            { value: 'undone', label: 'Not Done' }
          ]
        },
        {
          label: 'Labels',
          type: 'labels',
          param: 'label',
          modeParam: 'label_mode',
          labels: labels.map(label => label.get('label'))
        }
      ],
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon: 'list',
            configurableFields: {
              title: 'Title',
              project: 'Project',
              date_due: 'Due Date',
              assignee: 'Assignee'
            },
            visibleFields: cardVisibleFields,
            toggleFieldVisibility: toggleCardFieldVisibility
          },
          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon: 'table',
            configurableFields: {
              id: 'ID',
              project: 'Project',
              date_due: 'Due Date',
              assignee: 'Assignee'
            },
            visibleFields: tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility
          },
          [constants.VIEW_MODE_KANBAN]: {
            label: 'Kanban View',
            icon: 'sticky-note-o',
            configurableFields: {
              title: 'Title',
              project: 'Project',
              date_due: 'Due Date',
              assignee: 'Assignee'
            },
            visibleFields: kanbanVisibleFields,
            toggleFieldVisibility: toggleKanbanFieldVisibility
          },
          [constants.VIEW_MODE_CALENDAR]: {
            label: 'Calendar View',
            icon: 'calendar',
            configurableFields: {
              title: 'Title',
              project: 'Project',
              date_due: 'Due Date',
              assignee: 'Assignee'
            },
            visibleFields: calendarVisibleFields,
            toggleFieldVisibility: toggleCalendarFieldVisibility
          }
        },

        viewMode: viewMode,
        viewModeAction: value => updateRoutingState('list', 'view', value)
      }
    };

    return <ControlBar {...config} />;
  }
}
