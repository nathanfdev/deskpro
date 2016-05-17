import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  currentListParamsSelector,
  listFiltersSelector,
  currentViewModeSelector,
  currentContentSelector,
  peopleVisibleFieldsSelector,
  organizationVisibleFieldsSelector
} from '../../../Selectors/list';
import {
  applyParams,
  togglePeopleCardFieldVisibility,
  togglePeopleTableFieldVisibility,
  toggleOrgCardFieldVisibility,
  toggleOrgTableFieldVisibility
} from '../../../Actions/crmListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  content:                   currentContentSelector(state),
  filters:                   listFiltersSelector(state),
  currentParams:             currentListParamsSelector(state),
  viewMode:                  currentViewModeSelector(state),
  peopleVisibleFields:       peopleVisibleFieldsSelector(state),
  organizationVisibleFields: organizationVisibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {

  static propTypes = {
    content:                   PropTypes.string.isRequired,
    filters:                   PropTypes.array.isRequired,
    currentParams:             PropTypes.object.isRequired,
    viewMode:                  PropTypes.string.isRequired,
    peopleVisibleFields:       PropTypes.object,
    organizationVisibleFields: PropTypes.object
  };

  getPeopleConfig() {
    const { filters, currentParams, peopleVisibleFields } = this.props;

    return {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created:    { label: 'Created', icon: 'calendar' },
        name:            { label: 'Name', icon: 'sort-alpha-asc' },
        date_last_login: { label: 'Last login', icon: 'calendar' },
        organization:    { label: 'Organization', icon: 'building-o' }
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

            visibleFields:         peopleVisibleFields.get(constants.VIEW_MODE_CARD),
            toggleFieldVisibility: togglePeopleCardFieldVisibility
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

            visibleFields:         peopleVisibleFields.get(constants.VIEW_MODE_TABLE),
            toggleFieldVisibility: togglePeopleTableFieldVisibility
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };
  }

  getOrganizationConfig() {
    const { filters, currentParams, organizationVisibleFields } = this.props;

    return {
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

            visibleFields:         organizationVisibleFields.get(constants.VIEW_MODE_CARD),
            toggleFieldVisibility: toggleOrgCardFieldVisibility
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

            visibleFields:         organizationVisibleFields.get(constants.VIEW_MODE_TABLE),
            toggleFieldVisibility: toggleOrgTableFieldVisibility
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };
  }

  render() {
    const config = this.props.content === 'people' ? this.getPeopleConfig() : this.getOrganizationConfig();

    return <ControlBar {...config} />;
  }
}
