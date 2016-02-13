import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {
  listSortSelector, listOrderSelector, tableVisibleFieldsSelector, cardVisibleFieldsSelector,
  viewModeSelector, listParamsSelector
} from '../../Selectors/list';
import {
  toggleAll, setViewMode, setSort, setOrder, toggleTableFieldVisibility, toggleCardFieldVisibility, applyListParams
} from '../../Actions/listActions';
import { labelsSelector } from '../../Selectors/nav';

@connect(state => ({
  sort: listSortSelector(state),
  order: listOrderSelector(state),
  viewMode: viewModeSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  listParams: listParamsSelector(state),
  labels: labelsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    cardVisibleFields: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    listParams: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const config = {
      sorting: {
        options: {
          id: { label: 'ID', icon: 'calendar' },
          date_last_user_reply: { label: 'Last user reply date', icon: 'calendar' },
          urgency: { label: 'Urgency', icon: 'calendar-o' }
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      filtering: {
        filters: [
          { label: 'Date Created', type: 'date', fromParam: 'from', toParam: 'to' },
          {
            label: 'Labels',
            type: 'labels',
            param: 'labels',
            modeParam: 'labels_mode',
            labels: this.props.labels.toJS()
          },
          {
            label: 'Status', type: 'select', param: 'status', options: [
            {
              value: 'new', label: 'New', nested: [
              { value: 'very_new', label: 'Very new' },
              { value: 'not_so_new', label: 'Not so new' }
            ]
            },
            { value: 'awaiting_agent', label: 'Awaiting agent' },
            { value: 'closed', label: 'Closed' }
          ]
          }
        ],
        setParamsAction: applyListParams,
        state: this.props.listParams
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
              id: 'ID',
              urgency: 'Urgency',
              person: 'Person',
              person_email: 'Person email',
              agent: 'Agent',
              subject: 'Subject',
              status: 'Status',
              date_created: 'Date created',
              labels: 'Labels'
            },

            visibleFields: this.props.tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: setViewMode
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}