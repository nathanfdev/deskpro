import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { OrganizationsTable } from './OrganizationsTable';
import { PeopleTable } from './PeopleTable';
import {
  currentContentSelector,
  currentListOrderBySelector,
  currentListOrderDirSelector,
  peopleVisibleFieldsSelector,
  organizationVisibleFieldsSelector
} from '../../../../Selectors/list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { applyParams } from '../../../../Actions/crmListActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => ({
  content:                   currentContentSelector(state),
  people:                    collectionSelectorFactory('Person', 'crm')(state),
  organizations:             collectionSelectorFactory('Organization', 'crm')(state),
  orderBy:                   currentListOrderBySelector(state),
  orderDir:                  currentListOrderDirSelector(state),
  peopleVisibleFields:       peopleVisibleFieldsSelector(state),
  organizationVisibleFields: organizationVisibleFieldsSelector(state)
}))

export class CrmTableContainer extends Component {

  static propTypes = {
    dispatch:                  PropTypes.func.isRequired,
    content:                   PropTypes.string.isRequired,
    people:                    PropTypes.object,
    organizations:             PropTypes.object,
    linkedOrganizations:       PropTypes.object,
    orderBy:                   PropTypes.string.isRequired,
    orderDir:                  PropTypes.string.isRequired,
    peopleVisibleFields:       PropTypes.object,
    organizationVisibleFields: PropTypes.object
  };

  onSortTable = (orderBy, orderDir) => {
    this.props.dispatch(applyParams({ order_by: orderBy, order_dir: orderDir }));
  };

  render() {
    const { content, organizations, people, orderBy, orderDir } = this.props;
    const { peopleVisibleFields, organizationVisibleFields } = this.props;

    if (content === 'organizations') {
      return (
        <OrganizationsTable
          organizations={organizations}
          currentOrderBy={orderBy}
          currentOrderDir={orderDir}
          sortTable={this.onSortTable}
          visibleFields={organizationVisibleFields.get(constants.VIEW_MODE_TABLE)}
        />
      );
    }

    return (
      <PeopleTable
        people={people}
        organizations={organizations}
        currentOrderBy={orderBy}
        currentOrderDir={orderDir}
        sortTable={this.onSortTable}
        visibleFields={peopleVisibleFields.get(constants.VIEW_MODE_TABLE)}
      />
    );
  }
}
