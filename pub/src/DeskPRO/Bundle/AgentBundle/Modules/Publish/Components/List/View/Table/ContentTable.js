import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class ContentTable extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    ids: PropTypes.array.isRequired,
    elements: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    sortTable: PropTypes.func.isRequired,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired
  };

  renderRow(id) {
    const { elements, people } = this.props;
    const element = elements.get(id);

    return (
      <tr key={id}>
        <TdId visible>
          {id}
        </TdId>
        <Td visible>
          <PersonInTable person={people.get(element.get('person'))}/>
        </Td>
        <Td visible>
          <div className="dpw--timer"><FormattedRelative value={element.get('date_created')}/></div>
        </Td>
        <Td visible>
          <div className="dpw--timer"><FormattedRelative value={element.get('date_updated')}/></div>
        </Td>
        <Td visible>
          {element.get('status')}
        </Td>
        <Td visible>
          {element.get('labels') && element.get('labels').join(', ')}
        </Td>
        <Td className="item-title">
          <a href="#"><SlicedString string={element.get('title')}/></a>
        </Td>
      </tr>
    );
  }

  render() {
    const { ids, currentSort, currentOrder, sortTable } = this.props;

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
          <Th sort="person"
              title="Author"
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
          <Th sort="date_updated"
              title="Updated"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th sort="status"
              title="Status"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th title="Labels" visible/>
          <Th sort="title"
              title="Title"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
        </tr>
        </thead>
        <tbody>
        {ids && ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}