import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class CommentTable extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    content: PropTypes.string.isRequired,
    ids: PropTypes.array.isRequired,
    elements: PropTypes.object.isRequired,
    parents: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    sortTable: PropTypes.func.isRequired,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired
  };

  renderRow(id) {
    const { content, elements, people, parents } = this.props;
    const element = elements.get(id);
    const getParent = () => {
      switch (content) {
        case 'article_comments':
          return parents.get(element.get('article'));
        case 'download_comments':
          return parents.get(element.get('download'));
        case 'news_comments':
          return parents.get(element.get('news'));
        default:
      }
    };

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
          {element.get('status')}
        </Td>
        <Td className="item-title">
          <a href="#"><SlicedString string={getParent().get('title')}/></a>
        </Td>
        <Td className="item-title">
          <a href="#"><SlicedString string={element.get('content')}/></a>
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
          <Th sort="status"
              title="Status"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={sortTable}/>
          <Th title="Subject"
              visible/>
          <Th title="Content"
              visible/>
        </tr>
        </thead>
        <tbody>
        {ids && ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}