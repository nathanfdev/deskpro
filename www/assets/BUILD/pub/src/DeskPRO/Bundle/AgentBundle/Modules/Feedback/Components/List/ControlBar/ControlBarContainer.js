import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import {
  currentListSortSelector, currentListOrderSelector, currentListParamsSelector, currentViewModeSelector,
  visibleFieldsSelector
} from '../../../Selectors/list';
import { listFiltersSelector} from '../../../Selectors/filters';
import { setSort, setOrder, applyParams, toggleTableFieldVisibility, toggleCardFieldVisibility,
  storeDisplayFieldsToPersonSetting, updateDisplayFieldsToPersonSetting }
  from '../../../Actions/FeedbackListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  filterParams: currentListParamsSelector(state),
  filters: listFiltersSelector(state),
  viewMode: currentViewModeSelector(state),
  visibleFields: visibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filterParams: PropTypes.object.isRequired,
    filters: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired,
    visibleFields: PropTypes.object
  };

  render() {
    const config = {
      onMenuUnmount: applyParams,
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
              category: 'Category',
              date_created: 'Date created',
              labels: 'Labels'
            },

            visibleFields: this.props.visibleFields.get(constants.VIEW_MODE_CARD),
            toggleFieldVisibility: toggleCardFieldVisibility
          },
          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon: 'table',

            configurableFields: {
              id: 'ID',
              title: 'Title',
              person: 'Author',
              content: 'Content',
              status: 'Status',
              date_created: 'Date created',
              labels: 'Labels'
            },

            visibleFields: this.props.visibleFields.get(constants.VIEW_MODE_TABLE),
            toggleFieldVisibility: toggleTableFieldVisibility
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode),
        onViewFieldsMenuUnmount: this.props.visibleFields.get('fromDb') ? updateDisplayFieldsToPersonSetting : storeDisplayFieldsToPersonSetting
      }
    };
    return (
      <ControlBar {...config} />
    );
  }
}