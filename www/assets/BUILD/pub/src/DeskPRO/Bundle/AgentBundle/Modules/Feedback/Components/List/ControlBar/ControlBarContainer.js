import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  currentListParamsSelector,
  currentViewModeSelector,
  isCommentsSelector,
  visibleFieldsSelector
} from '../../../Selectors/list';
import { listFiltersSelector } from '../../../Selectors/filters';
import {
  applyParams,
  toggleTableFieldVisibility,
  toggleCardFieldVisibility,
  storeDisplayFieldsToPersonSetting,
  updateDisplayFieldsToPersonSetting
}
  from '../../../Actions/FeedbackListActions';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';
import { constants } from '../../../../../Constants/Constants';
import { ControlBar } from '../../../../Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  currentParams: currentListParamsSelector(state),
  filters:       listFiltersSelector(state),
  isComments:    isCommentsSelector(state),
  viewMode:      currentViewModeSelector(state),
  visibleFields: visibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    currentParams: PropTypes.object.isRequired,
    filters:       PropTypes.array.isRequired,
    isComments:    PropTypes.bool,
    viewMode:      PropTypes.string.isRequired,
    visibleFields: PropTypes.object
  };

  render() {
    const sorting = { date_created: { label: 'Date', icon: 'calendar' } };
    const { filters, currentParams } = this.props;

    if (!this.props.isComments) {
      Object.assign(sorting, {
        num_ratings:  { label: 'Votes', icon: 'calendar' },
        total_rating: { label: 'Rating', icon: 'calendar-o' }
      });
    }

    const config = {
      applyParams,
      sorting,
      currentParams,
      filters,

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon:  'list',

            configurableFields: {
              id:           'ID',
              category:     'Category',
              date_created: 'Date created',
              labels:       'Labels'
            },

            visibleFields:         this.props.visibleFields.get(constants.VIEW_MODE_CARD),
            toggleFieldVisibility: toggleCardFieldVisibility
          },

          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon:  'table',

            configurableFields: {
              id:           'ID',
              title:        'Title',
              person:       'Author',
              content:      'Content',
              status:       'Status',
              date_created: 'Date created',
              labels:       'Labels'
            },

            visibleFields:         this.props.visibleFields.get(constants.VIEW_MODE_TABLE),
            toggleFieldVisibility: toggleTableFieldVisibility
          }
        },

        viewMode:                this.props.viewMode,
        viewModeAction:          (mode) => updateRoutingState('list', 'view', mode),
        onViewFieldsMenuUnmount: this.props.visibleFields.get('fromDb') ?
                                   updateDisplayFieldsToPersonSetting : storeDisplayFieldsToPersonSetting
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
