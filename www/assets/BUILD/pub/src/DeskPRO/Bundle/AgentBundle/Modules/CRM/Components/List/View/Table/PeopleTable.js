import React, { Component, PropTypes } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

@injectIntl
export class PeopleTable extends Component {

  static propTypes = {
    intl:            intlShape.isRequired,
    people:          PropTypes.object,
    organizations:   PropTypes.object,
    sortTable:       PropTypes.func.isRequired,
    currentOrderBy:  PropTypes.string.isRequired,
    currentOrderDir: PropTypes.string.isRequired
  };

  renderOrganization = (organizations, id) =>
    <Td visible>
      {organizations.get(id) ? organizations.get(id).get('name') : ''}
    </Td>;

  render() {
    const { people, currentOrderBy, currentOrderDir, sortTable, organizations } = this.props;
    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              title="ID"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="timezone"
              title="TZ"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="organization"
              title="Organization"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="first_name"
              title="First name"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="last_name"
              title="Last name"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="primary_email"
              title="Email"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="date_created"
              title="Created"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
          <Th sort="date_last_login"
              title="Last login"
              visible
              orderDir={currentOrderDir}
              orderBy={currentOrderBy}
              onChange={sortTable} />
        </tr>
        </thead>
        <tbody>
        {people && people.map((element, index) =>
            <tr key={index}>
              <TdId visible>
                {element.get('id')}
              </TdId>
              <Td visible>
                {element.get('timezone')}
              </Td>
              {this.renderOrganization(organizations, element.get('organization'))}
              <Td visible>
                {element.get('first_name')}
              </Td>
              <Td visible>
                {element.get('last_name')}
              </Td>
              <Td visible>
                {element.get('primary_email')}
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.get('date_created')} /></div>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.get('date_last_login')} /></div>
              </Td>
            </tr>
        )}
        </tbody>
      </Table>
    );
  }
}
