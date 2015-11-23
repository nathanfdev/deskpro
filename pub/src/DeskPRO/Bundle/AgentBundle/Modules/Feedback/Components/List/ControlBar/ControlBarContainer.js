import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import {
  currentListSortSelector, currentListOrderSelector, currentListParamsSelector, currentViewModeSelector,
  tableVisibleFieldsSelector, cardVisibleFieldsSelector, listFiltersSelector
} from '../../../Selectors/list';
import { toggleMassAction, setSort, setOrder, applyParams, toggleTableFieldVisibility, toggleCardFieldVisibility, storeDisplayFieldsToPersonSetting }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  count: state.Feedback.list.get('selected').size,
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  filterParams: currentListParamsSelector(state),
  filters: listFiltersSelector(state),
  viewMode: currentViewModeSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    count: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filterParams: PropTypes.object.isRequired,
    filters: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    cardVisibleFields: PropTypes.object.isRequired
  };

  render() {
    const config = {
      onMenuUnmount: applyParams,
      checkbox: {
        count: this.props.count,
        action: toggleMassAction
      },
      sorting: {
        options: {
          date_created: { label: 'Date', icon: 'calendar' },
          total_rating: { label: 'Rating', icon: 'calendar-o' },
          num_ratings: { label: 'Votes', icon: 'calendar' }
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      filtering: {
        filters: this.props.filters,
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
              custom_category: 'Category',
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
              title: 'Title',
              person: 'Person',
              content: 'Content',
              status: 'Status',
              date_created: 'Date created',
              labels: 'Labels'
            },

            visibleFields: this.props.tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode),
        onViewFieldsMenuUnmount: storeDisplayFieldsToPersonSetting
      }
    };
    return (
      <ControlBar {...config} />
    );
  }
}