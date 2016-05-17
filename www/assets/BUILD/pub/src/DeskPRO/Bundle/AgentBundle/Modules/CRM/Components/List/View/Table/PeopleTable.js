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
    currentOrderDir: PropTypes.string.isRequired,
    visibleFields:   PropTypes.object
  };

  isVisible(field) {
    return this.props.visibleFields.includes(field);
  }

  render() {
    const { people, currentOrderBy, currentOrderDir, sortTable, organizations } = this.props;
    return (
      <Table>
        <thead>
        <tr>
          <Th
            sort="id"
            title="ID"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('id')}
          />
          <Th
            sort="timezone"
            title="TZ"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('timezone')}
          />
          <Th
            sort="organization"
            title="Organization"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('organization')}
          />
          <Th
            sort="first_name"
            title="First name"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('first_name')}
          />
          <Th
            sort="last_name"
            title="Last name"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('last_name')}
          />
          <Th
            sort="primary_email"
            title="Email"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('primary_email')}
          />
          <Th
            sort="date_created"
            title="Created"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('date_created')}
          />
          <Th
            sort="date_last_login"
            title="Last login"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
            visible={this.isVisible('date_last_login')}
          />
        </tr>
        </thead>
        <tbody>
        {people && people.map((element, index) =>
            <tr key={index}>
              <TdId visible={this.isVisible('id')}>
                {element.get('id')}
              </TdId>
              <Td visible={this.isVisible('timezone')}>
                {element.get('timezone')}
              </Td>
              <Td visible={this.isVisible('organization')}>
                {organizations.get(element.get('organization'))
                  && organizations.get(element.get('organization')).get('name')}
              </Td>
              <Td visible={this.isVisible('first_name')}>
                {element.get('first_name')}
              </Td>
              <Td visible={this.isVisible('last_name')}>
                {element.get('last_name')}
              </Td>
              <Td visible={this.isVisible('primary_email')}>
                {element.get('primary_email')}
              </Td>
              <Td visible={this.isVisible('date_created')}>
                <div className="dpw--timer"><FormattedRelative value={element.get('date_created')} /></div>
              </Td>
              <Td visible={this.isVisible('date_last_login')}>
                <div className="dpw--timer"><FormattedRelative value={element.get('date_last_login')} /></div>
              </Td>
            </tr>
        )}
        </tbody>
      </Table>
    );
  }
}
