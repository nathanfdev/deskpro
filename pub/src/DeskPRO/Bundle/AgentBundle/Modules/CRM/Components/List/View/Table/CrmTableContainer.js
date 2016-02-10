import React, {Component, PropTypes} from 'react';
import { OrganizationsTable } from './OrganizationsTable';
import { PeopleTable } from './PeopleTable';
import { currentContentSelector, currentListSortSelector, currentListOrderSelector }
  from '../../../../Selectors/list';
import { peopleSelector } from '../../../../Selectors/recordStores';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { applyParams} from '../../../../Actions/crmListActions';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    content: currentContentSelector(state),
    people: peopleSelector(state),
    organizations: collectionSelectorFactory('Organization', 'crm')(state),
    currentOrder: currentListOrderSelector(state),
    currentSort: currentListSortSelector(state)
  });
})
export class CrmTableContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    people: PropTypes.array,
    linkedOrganizations: PropTypes.object,
    organizations: PropTypes.aray,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired
  };

  sortTable(param, order) {
    this.props.dispatch(applyParams({ sort: param, order }));
  }

  render() {
    const {content, organizations, people, currentSort, currentOrder } = this.props;
    if (content === 'organizations') {
      return (
        <OrganizationsTable organizations={organizations}
                            currentSort={currentSort}
                            currentOrder={currentOrder}
                            sortTable={this.sortTable.bind(this)}/>
      );
    }
    return (
      <PeopleTable people={people}
                   organizations={organizations}
                   currentSort={currentSort}
                   currentOrder={currentOrder}
                   sortTable={this.sortTable.bind(this)}/>
    );
  }
}