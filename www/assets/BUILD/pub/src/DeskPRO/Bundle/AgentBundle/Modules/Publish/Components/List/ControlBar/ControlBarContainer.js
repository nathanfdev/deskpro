import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { currentListParamsSelector, listFiltersSelector, currentViewModeSelector } from '../../../Selectors/list';
import { applyParams } from '../../../Actions/publishListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  currentParams: currentListParamsSelector(state),
  filters:       listFiltersSelector(state),
  viewMode:      currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    currentParams: PropTypes.object.isRequired,
    filters:       PropTypes.array.isRequired,
    viewMode:      PropTypes.string.isRequired
  };

  render = () => {
    const { currentParams, filters } = this.props;
    const config = {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created: { label: 'Created', icon: 'calendar' },
        date_updated: { label: 'Updated', icon: 'calendar-o' },
        person:       { label: 'Author', icon: 'calendar' }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon:  'list',

            configurableFields: {
              id:           'ID',
              date_created: 'Date created'
            }
          },

          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon:  'table',

            configurableFields: {
              id:           'ID',
              title:        'Title',
              person:       'Person',
              content:      'Content',
              date_created: 'Date created'
            }
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };
    return (
      <ControlBar {...config} />
    );
  }
}
