import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { applyFilters } from '../../Actions/listActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { loadAll, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

import {
  currentViewModeSelector,
  cardFieldsSelector,
  tableFieldsSelector,
  calendarFieldsSelector,
  kanbanFieldsSelector,
  listParamsFiltersSelector
} from '../../Selectors/list';
import {
  toggleFieldVisibility,
  changeFieldOrder
} from '../../Actions/listActions';

@connect(state => ({
  viewMode:       currentViewModeSelector(state),
  cardFields:     cardFieldsSelector(state),
  tableFields:    tableFieldsSelector(state),
  kanbanFields:   kanbanFieldsSelector(state),
  calendarFields: calendarFieldsSelector(state),
  currentParams:  listParamsFiltersSelector(state),
  labels:         allSelectorFactory('TaskLabel')(state)
}), {
  toggleFieldVisibility,
  changeFieldOrder,
  loadAll,
  applyFilters
})
export class ControlBarContainer extends React.Component {

  static propTypes = {
    viewMode:              PropTypes.string.isRequired,
    cardFields:            PropTypes.object.isRequired,
    tableFields:           PropTypes.object.isRequired,
    kanbanFields:          PropTypes.object.isRequired,
    calendarFields:        PropTypes.object.isRequired,
    currentParams:         PropTypes.object.isRequired,
    labels:                PropTypes.object.isRequired,
    loadAll:               PropTypes.func.isRequired,
    toggleFieldVisibility: PropTypes.func.isRequired,
    changeFieldOrder:      PropTypes.func.isRequired,
    applyFilters:          PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.loadAll('TaskLabel');
  }

  render() {
    const { labels = [], currentParams, viewMode } = this.props;
    const { cardFields, tableFields, kanbanFields, calendarFields } = this.props;

    const config = {
      applyParams: this.props.applyFilters,
      currentParams,
      sorting:     {
        list: {
          label: 'List',
          icon:  'list'
        },
        project: {
          label: 'Project',
          icon:  'briefcase'
        },
        date_due: {
          label: 'Due Date',
          icon:  'calendar'
        },
        date_done: {
          label: 'Done Date',
          icon:  'calendar'
        },
        date_created: {
          label: 'Created Date',
          icon:  'calendar'
        },
        assignee: {
          label: 'Assignee',
          icon:  'user'
        }
      },
      filters: [
        {
          label:     'Date Created',
          type:      'date',
          fromParam: 'created_from',
          toParam:   'created_to'
        },
        {
          label:     'Date Due',
          type:      'date',
          fromParam: 'due_from',
          toParam:   'due_to'
        },
        {
          label:     'Date Done',
          type:      'date',
          fromParam: 'done_from',
          toParam:   'done_to'
        },
        {
          label:    'Status',
          type:     'select',
          param:    'done',
          multiple: false,
          options:  [
            {
              value: 'done',
              label: 'Done'
            },
            {
              value: 'undone',
              label: 'Not Done'
            }
          ]
        },
        {
          label:     'Labels',
          type:      'labels',
          param:     'label',
          modeParam: 'label_mode',
          labels:    labels.map(label => label.get('label'))
        }
      ],
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: cardFields
          },
          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: tableFields
          },
          [constants.VIEW_MODE_KANBAN]: {
            label:  'Kanban View',
            icon:   'sticky-note-o',
            fields: kanbanFields
          },
          [constants.VIEW_MODE_CALENDAR]: {
            label:  'Calendar View',
            icon:   'calendar',
            fields: calendarFields
          }
        },

        viewMode,
        viewModeAction:        value => updateRoutingState('list', 'view', value),
        toggleFieldVisibility: this.props.toggleFieldVisibility,
        changeFieldOrder:      this.props.changeFieldOrder
      }
    };

    return <ControlBar {...config} />;
  }
}
