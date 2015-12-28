import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

@injectIntl
export class PeopleTable extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.object,
    organizations: PropTypes.object,
    sortTable: PropTypes.func.isRequired,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired
  };

  renderOrganisation(organizations, id) {
    return (
      <Td visible>
        {organizations.get(id) ? organizations.get(id).get('name') : ''}
      </Td>
    );
  }

  render() {
    const { people, currentSort, currentOrder, sortTable, organizations} = this.props;
    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              title="ID"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="timezone"
              title="TZ"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="organization"
              title="Organization"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="first_name"
              title="First name"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="last_name"
              title="Last name"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="primary_email"
              title="Email"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="date_created"
              title="Created"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="date_last_login"
              title="Last login"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
        </tr>
        </thead>
        <tbody>
        {people.map((element, index) =>
            <tr key={index}>
              <TdId visible>
                {element.id}
              </TdId>
              <Td visible>
                {element.timezone}
              </Td>
              {this.renderOrganisation(organizations, element.organization)}
              <Td visible>
                {element.first_name}
              </Td>
              <Td visible>
                {element.last_name}
              </Td>
              <Td visible>
                {element.primary_email}
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_created}/></div>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_last_login}/></div>
              </Td>
            </tr>
        )}
        </tbody>
      </Table>
    );
  }
}
