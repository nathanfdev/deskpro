import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { applyOrderBy, applyOrderDir, applyFilters } from '../../Actions/listActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { loadAll, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

import {
  currentViewModeSelector,
  currentOrderBySelector,
  currentOrderDirSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  listParamsFiltersSelector,
  selectedCountSelector,
} from '../../Selectors/list';
import {
  toggleCardFieldVisibility,
  toggleTableFieldVisibility,
  toggleKanbanFieldVisibility,
  toggleCalendarFieldVisibility,
} from '../../Actions/listActions';

@connect(state => ({
  viewMode: currentViewModeSelector(state),
  orderBy: currentOrderBySelector(state),
  orderDir: currentOrderDirSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state),
  listFilters: listParamsFiltersSelector(state),
  labels: allSelectorFactory('TaskLabel')(state)
}))
export class ControlBarContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
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
    props.dispatch(loadAll('TaskLabel'));
  }

  render() {
    const { orderBy, orderDir, labels = [], listFilters, viewMode } = this.props;
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

        orderBy: orderBy,
        orderByAction: applyOrderBy,

        orderDir: orderDir,
        orderDirAction: applyOrderDir
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
