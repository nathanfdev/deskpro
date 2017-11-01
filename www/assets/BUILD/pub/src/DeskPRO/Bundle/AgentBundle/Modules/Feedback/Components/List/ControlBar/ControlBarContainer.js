import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import {
  currentListParamsSelector,
  currentViewModeSelector,
  isCommentsSelector,
  tableFieldsSelector,
  cardFieldsSelector
} from '../../../Selectors/list';
import { listFiltersSelector } from '../../../Selectors/filters';
import {
  applyParams,
  toggleFieldVisibility,
  changeFieldOrder,
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
  cardFields:    cardFieldsSelector(state),
  tableFields:   tableFieldsSelector(state)
}), {
  applyParams,
  toggleFieldVisibility,
  changeFieldOrder
})
export class ControlBarContainer extends Component {
  static propTypes = {
    currentParams:         PropTypes.object.isRequired,
    filters:               PropTypes.array.isRequired,
    isComments:            PropTypes.bool,
    viewMode:              PropTypes.string.isRequired,
    cardFields:            PropTypes.object.isRequired,
    tableFields:           PropTypes.object.isRequired,
    applyParams:           PropTypes.func.isRequired,
    toggleFieldVisibility: PropTypes.func.isRequired,
    changeFieldOrder:      PropTypes.func.isRequired
  };

  render() {
    const sorting = {
      date_created: {
        label: 'Date',
        icon:  'calendar'
      }
    };
    const { filters, currentParams, applyParams, toggleFieldVisibility, changeFieldOrder, cardFields, tableFields }
            = this.props;

    if (!this.props.isComments) {
      Object.assign(sorting, {
        num_ratings: {
          label: 'Votes',
          icon:  'calendar'
        },
        total_rating: {
          label: 'Rating',
          icon:  'calendar-o'
        }
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
            label:  'Card View',
            icon:   'list',
            fields: cardFields
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: tableFields
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility,
        changeFieldOrder
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
