import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { applySort, applyOrder, applyFilters } from '../../Actions/listActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { allTaskLabelsSelector } from '../../RecordStores/Selectors/taskLabelSelectors';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import {
  currentViewModeSelector,
  currentSortSelector,
  currentOrderSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  listParamsFiltersSelector,
} from '../../Selectors/list';
import {
  toggleCardFieldVisibility,
  toggleTableFieldVisibility,
  toggleKanbanFieldVisibility,
  toggleCalendarFieldVisibility
} from '../../Actions/listActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state),
  sort: currentSortSelector(state),
  order: currentOrderSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state),
  listFilters: listParamsFiltersSelector(state),
  labels: allTaskLabelsSelector(state)
}))
export class ControlBarContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selectedCount: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    viewMode: PropTypes.string.isRequired,
    cardVisibleFields: PropTypes.object.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    kanbanVisibleFields: PropTypes.object.isRequired,
    calendarVisibleFields: PropTypes.object.isRequired,
    listFilters: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadAllTaskLabels());
  }

  render() {
    const { sort, order, labels = [], listFilters, viewMode } = this.props;
    const { cardVisibleFields, tableVisibleFields, kanbanVisibleFields, calendarVisibleFields } = this.props;

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

        sort: sort,
        sortAction: applySort,

        order: order,
        orderAction: applyOrder
      },
      filtering: {
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
          {label: 'Status', type: 'select', param: 'done', multiple: false, options: [
            {value: 'done', label: 'Done'},
            {value: 'undone', label: 'Not Done'}
          ]},
          {
            label: 'Labels',
            type: 'labels',
            param: 'label',
            modeParam: 'label_mode',
            labels: labels.map(label => label.get('label'))
          }
        ],

        state: listFilters,
        setParamsAction: applyFilters
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
            visibleFields: cardVisibleFields,
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
            visibleFields: tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility
          },
          [constants.VIEW_MODE_KANBAN]: {
            label: 'Kanban View',
            icon: 'sticky-note-o',
            configurableFields: {
              subject: 'Subject',
              status: 'Status'
            },
            visibleFields: kanbanVisibleFields,
            toggleFieldVisibility: toggleKanbanFieldVisibility
          },
          [constants.VIEW_MODE_CALENDAR]: {
            label: 'Calendar View',
            icon: 'calendar',
            configurableFields: {
              subject: 'Subject',
              status: 'Status'
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
