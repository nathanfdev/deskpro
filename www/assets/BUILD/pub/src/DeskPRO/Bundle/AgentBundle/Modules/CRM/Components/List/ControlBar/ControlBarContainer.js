import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import {
  currentListParamsSelector,
  listFiltersSelector,
  currentViewModeSelector,
  currentContentSelector,
  peopleFieldsSelector,
  orgFieldsSelector,
} from '../../../Selectors/list';
import {
  applyParams,
  togglePeopleFieldVisibility,
  toggleOrgFieldVisibility,
  changePeopleFieldOrder,
  changeOrgFieldOrder
} from '../../../Actions/crmListActions';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';
import { constants } from '../../../../../Constants/Constants';
import { ControlBar } from '../../../../../Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  content:       currentContentSelector(state),
  filters:       listFiltersSelector(state),
  currentParams: currentListParamsSelector(state),
  viewMode:      currentViewModeSelector(state),
  peopleFields:  peopleFieldsSelector(state),
  orgFields:     orgFieldsSelector(state)
}), {
  togglePeopleFieldVisibility,
  toggleOrgFieldVisibility,
  changePeopleFieldOrder,
  changeOrgFieldOrder,
  applyParams
})
export class ControlBarContainer extends Component {

  static propTypes = {
    content:                     PropTypes.string.isRequired,
    filters:                     PropTypes.array.isRequired,
    currentParams:               PropTypes.object.isRequired,
    viewMode:                    PropTypes.string.isRequired,
    peopleFields:                PropTypes.object.isRequired,
    orgFields:                   PropTypes.object.isRequired,
    togglePeopleFieldVisibility: PropTypes.func.isRequired,
    toggleOrgFieldVisibility:    PropTypes.func.isRequired,
    changePeopleFieldOrder:      PropTypes.func.isRequired,
    changeOrgFieldOrder:         PropTypes.func.isRequired,
    applyParams:                 PropTypes.func.isRequired
  };

  getPeopleConfig() {
    const { filters, currentParams, peopleFields, applyParams } = this.props;

    return {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created: {
          label: 'Created',
          icon:  'calendar'
        },
        name: {
          label: 'Name',
          icon:  'sort-alpha-asc'
        },
        date_last_login: {
          label: 'Last login',
          icon:  'calendar'
        },
        organization: {
          label: 'Organization',
          icon:  'building-o'
        }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: peopleFields.get(constants.VIEW_MODE_CARD)
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: peopleFields.get(constants.VIEW_MODE_TABLE)
          }
        },

        viewMode:              this.props.viewMode,
        viewModeAction:        (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility: this.props.togglePeopleFieldVisibility,
        changeFieldOrder:      this.props.changePeopleFieldOrder
      }
    };
  }

  getOrganizationConfig() {
    const { filters, currentParams, orgFields } = this.props;

    return {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created: {
          label: 'Created',
          icon:  'calendar'
        },
        name: {
          label: 'Name',
          icon:  'sort-alpha-asc'
        }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: orgFields.get(constants.VIEW_MODE_CARD)
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: orgFields.get(constants.VIEW_MODE_TABLE)
          }
        },

        viewMode:              this.props.viewMode,
        viewModeAction:        (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility: this.props.toggleOrgFieldVisibility,
        changeFieldOrder:      this.props.changeOrgFieldOrder
      }
    };
  }

  render() {
    const config = this.props.content === 'people' ? this.getPeopleConfig() : this.getOrganizationConfig();

    return <ControlBar {...config} />;
  }
}
