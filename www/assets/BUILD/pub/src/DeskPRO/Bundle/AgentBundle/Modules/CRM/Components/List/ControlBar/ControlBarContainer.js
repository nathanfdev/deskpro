import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { currentListParamsSelector, listFiltersSelector, currentViewModeSelector, currentContentSelector, visibleFieldsSelector } from '../../../Selectors/list';
import { applyParams, toggleTableFieldVisibility, toggleCardFieldVisibility } from '../../../Actions/crmListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  content:       currentContentSelector(state),
  filters:       listFiltersSelector(state),
  currentParams: currentListParamsSelector(state),
  viewMode:      currentViewModeSelector(state),
  visibleFields: visibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {

  static propTypes = {
    content:       PropTypes.string.isRequired,
    filters:       PropTypes.array.isRequired,
    currentParams: PropTypes.object.isRequired,
    viewMode:      PropTypes.string.isRequired,
    visibleFields: PropTypes.object
  };

  render() {
    const { content, filters, currentParams, visibleFields } = this.props;
    const config = {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created: { label: 'Created', icon: 'calendar' },
        name:         { label: 'Name', icon: 'sort-alpha-asc' }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon:  'list',

            configurableFields: {
              id:           'ID',
              date_created: 'Date created'
            },

            visibleFields:         visibleFields.get(constants.VIEW_MODE_CARD),
            toggleFieldVisibility: toggleCardFieldVisibility
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
            },

            visibleFields:         visibleFields.get(constants.VIEW_MODE_TABLE),
            toggleFieldVisibility: toggleTableFieldVisibility
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };

    if (content === 'people') {
      Object.assign(config.sorting, {
        date_last_login: { label: 'Last login', icon: 'calendar' },
        organization:    { label: 'Organization', icon: 'building-o' }
      });
    }

    return <ControlBar {...config} />;
  }
}
