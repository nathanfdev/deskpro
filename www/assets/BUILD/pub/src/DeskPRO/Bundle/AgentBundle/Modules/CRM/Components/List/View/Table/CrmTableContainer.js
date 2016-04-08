import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { OrganizationsTable } from './OrganizationsTable';
import { PeopleTable } from './PeopleTable';
import { currentContentSelector, currentListOrderBySelector, currentListOrderDirSelector }
  from '../../../../Selectors/list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { applyParams } from '../../../../Actions/crmListActions';

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
    orderDir:            PropTypes.string.isRequired
  };

  sortTable = (orderBy, orderDir) => {
    this.props.dispatch(applyParams({ order_by: orderBy, order_dir: orderDir }));
  };

  render() {
    const { content, organizations, people, orderBy, orderDir } = this.props;

    if (content === 'organizations') {
      return (
        <OrganizationsTable organizations={organizations}
                            currentOrderBy={orderBy}
                            currentOrderDir={orderDir}
                            sortTable={this.sortTable} />
      );
    }

    return (
      <PeopleTable people={people}
                   organizations={organizations}
                   currentOrderBy={orderBy}
                   currentOrderDir={orderDir}
                   sortTable={this.sortTable} />
    );
  }
}
