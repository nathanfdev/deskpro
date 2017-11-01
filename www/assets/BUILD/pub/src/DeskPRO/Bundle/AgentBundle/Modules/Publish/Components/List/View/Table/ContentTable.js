import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class ContentTable extends Component {
  static propTypes = {
    intl:      intlShape.isRequired,
    ids:       PropTypes.array.isRequired,
    elements:  PropTypes.object.isRequired,
    people:    PropTypes.object.isRequired,
    sortTable: PropTypes.func.isRequired,
    orderBy:   PropTypes.string.isRequired,
    orderDir:  PropTypes.string.isRequired
  };

  renderRow = element => {
    const { people } = this.props;
    const id = element.get('id');

    return (
      <tr key={id}>
        <TdId visible>
          {id}
        </TdId>
        <Td visible>
          <PersonInTable person={people.get(element.get('person'))} />
        </Td>
        <Td visible>
          <div className="dpw--timer"><FormattedRelative value={element.get('date_created')} /></div>
        </Td>
        <Td visible>
          <div className="dpw--timer"><FormattedRelative value={element.get('date_updated')} /></div>
        </Td>
        <Td visible>
          {element.get('status')}
        </Td>
        <Td visible>
          {element.get('labels') && element.get('labels').join(', ')}
        </Td>
        <Td className="item-title">
          <a href="#"><SlicedString string={element.get('title')} /></a>
        </Td>
      </tr>
    );
  };

  render = () => {
    const { ids, elements, orderBy, orderDir, sortTable } = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th
            sort="id"
            title="ID"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
          <Th
            sort="person"
            title="Author"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
          <Th
            sort="date_created"
            title="Created"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
          <Th
            sort="date_updated"
            title="Updated"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
          <Th
            sort="status"
            title="Status"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
          <Th
            title="Labels"
            visible
          />
          <Th
            sort="title"
            title="Title"
            visible
            orderBy={orderBy}
            orderDir={orderDir}
            onChange={sortTable}
          />
        </tr>
        </thead>
        <tbody>
        {ids && ids.map(id => this.renderRow(elements.get(id)))}
        </tbody>
      </Table>
    );
  };

}
