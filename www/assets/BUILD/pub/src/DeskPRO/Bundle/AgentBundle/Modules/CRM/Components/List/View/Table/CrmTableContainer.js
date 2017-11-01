import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { OrganizationsTable } from './OrganizationsTable';
import { PeopleTable } from './PeopleTable';
import {
  currentContentSelector,
  currentListOrderBySelector,
  currentListOrderDirSelector
} from '../../../../Selectors/list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { applyParams } from '../../../../Actions/crmListActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => ({
  content:       currentContentSelector(state),
  people:        collectionSelectorFactory('Person', 'crm')(state),
  organizations: collectionSelectorFactory('Organization', 'crm')(state),
  orderBy:       currentListOrderBySelector(state),
  orderDir:      currentListOrderDirSelector(state)
}))

export class CrmTableContainer extends Component {

  static propTypes = {
    dispatch:            PropTypes.func.isRequired,
    content:             PropTypes.string.isRequired,
    people:              PropTypes.object,
    organizations:       PropTypes.object,
    linkedOrganizations: PropTypes.object,
    orderBy:             PropTypes.string.isRequired,
    orderDir:            PropTypes.string.isRequired,
    peopleFields:        PropTypes.object.isRequired,
    orgFields:           PropTypes.object.isRequired
  };

  onSortTable = (orderBy, orderDir) => {
    this.props.dispatch(applyParams({
      order_by:  orderBy,
      order_dir: orderDir
    }));
  };

  render() {
    const { content, organizations, people, orderBy, orderDir } = this.props;
    const { peopleFields, orgFields } = this.props;

    if (content === 'Organization') {
      return (
        <OrganizationsTable
          organizations={organizations}
          currentOrderBy={orderBy}
          currentOrderDir={orderDir}
          sortTable={this.onSortTable}
          fields={orgFields}
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
        fields={peopleFields}
      />
    );
  }
}
