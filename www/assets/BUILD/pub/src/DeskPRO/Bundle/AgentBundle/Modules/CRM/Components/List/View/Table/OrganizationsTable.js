import React, { Component, PropTypes } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class OrganizationsTable extends Component {

  static propTypes = {
    intl:            intlShape.isRequired,
    organizations:   PropTypes.object,
    sortTable:       PropTypes.func.isRequired,
    currentOrderBy:  PropTypes.string.isRequired,
    currentOrderDir: PropTypes.string.isRequired
  };

  render() {
    const { organizations, currentOrderDir, currentOrderBy, sortTable } = this.props;
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
          />
          <Th
            sort="date_created"
            title="Created"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
          />
          <Th
            sort="importance"
            title="Importance"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
          />
          <Th
            sort="name"
            title="Name"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
          />
          <Th
            sort="summary"
            title="Summary"
            visible
            orderDir={currentOrderDir}
            orderBy={currentOrderBy}
            onChange={sortTable}
          />
        </tr>
        </thead>
        <tbody>
        {organizations && organizations.map((element, index) =>
            <tr key={index}>
              <TdId visible>
                {element.get('id')}
              </TdId>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.get('date_created')} /></div>
              </Td>
              <Td visible>
                {element.get('importance')}
              </Td>
              <Td className="item-title">
                <a href="#"><SlicedString string={element.get('name')} /></a>
              </Td>
              <Td className="item-title">
                <a href="#"><SlicedString string={element.get('summary')} /></a>
              </Td>
            </tr>
        )}
        </tbody>
      </Table>
    );
  }
}
