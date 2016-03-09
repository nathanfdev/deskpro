import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import {
  currentListOrderBySelector, currentListOrderDirSelector, currentListParamsSelector, currentViewModeSelector,
  visibleFieldsSelector
} from '../../../Selectors/list';
import { listFiltersSelector} from '../../../Selectors/filters';
import { setOrderBy, setOrderDir, applyParams, toggleTableFieldVisibility, toggleCardFieldVisibility,
  storeDisplayFieldsToPersonSetting, updateDisplayFieldsToPersonSetting }
  from '../../../Actions/FeedbackListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  orderBy: currentListOrderBySelector(state),
  orderDir: currentListOrderDirSelector(state),
  currentParams: currentListParamsSelector(state),
  filters: listFiltersSelector(state),
  viewMode: currentViewModeSelector(state),
  visibleFields: visibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
    currentParams: PropTypes.object.isRequired,
    filters: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired,
    visibleFields: PropTypes.object
  };

  render() {
    const config = {
      applyParams: applyParams,
      currentParams: this.props.currentParams,
      sorting: {
        options: {
          date_created: { label: 'Date', icon: 'calendar' },
          total_rating: { label: 'Rating', icon: 'calendar-o' },
          num_ratings: { label: 'Votes', icon: 'calendar' }
        },
        orderBy: this.props.orderBy,
        orderDir: this.props.orderDir,
        orderByAction: setOrderBy,
        orderDirAction: setOrderDir
      },
      filtering: {
        filters: this.props.filters
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