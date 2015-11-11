import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { toggleMassAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { currentListSortSelector, currentListOrderSelector, currentListParamsSelector, currentViewModeSelector }
  from '../../../Selectors/list';
import { setSort, setOrder, applyParams } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  count: state.Feedback.list.get('selected').size,
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  filterParams: currentListParamsSelector(state),
  viewMode: currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    count: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filterParams: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
  };

  render() {
    const config = {
      checkbox: {
        count: this.props.count,
        action: toggleMassAction
      },
      sorting: {
        options: {
          date_created: {label: 'Date', icon: 'calendar'},
          total_rating: {label: 'Rating', icon: 'calendar-o'},
          num_ratings:  {label: 'Votes', icon: 'calendar'}
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      filtering: {
        filters: [
          {label: 'Type', type: 'select', param: 'type', options: [
            {value: 'type_1', label: 'Type 1'}
          ]},
          {label: 'Status', type: 'select', param: 'status', options: [
            {value: 'new', label: 'New', nested: [
              {value: 'very_new', label: 'Very new'},
              {value: 'not_so_new', label: 'Not so new'}
            ]},
            {value: 'awaiting_agent', label: 'Awaiting agent'},
            {value: 'closed', label: 'Closed'}
          ]},
          {label: 'Category', type: 'select', param: 'category', options: [
            {value: 'mac', label: 'Mac'},
            {value: 'linux', label: 'Linux'},
            {value: 'windows', label: 'Windows'}
          ]},
          {label: 'Date', type: 'date', fromParam: 'from', toParam: 'to'},
          {label: 'Labels', type: 'labels', param: 'labels', modeParam: 'labels_mode', labels: [
            'label 1', 'label 2', 'label 3'
          ]}
        ],
        setParamsAction: applyParams,
        state: this.props.filterParams
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

            visibleFields: ['id', 'urgency', 'person'],
            toggleFieldVisibility: () => ({})
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

            visibleFields: ['id', 'urgency', 'person'],
            toggleFieldVisibility: () => ({})
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}