import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { contentSelector, articlesSelector, newsSelector, downloadsSelector, currentListSortSelector, currentListOrderSelector }
  from '../../../../Selectors/list';
import { peopleSelector }
  from '../../../../Selectors/recordStores';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { applyParams } from '../../../../Actions/publishListActions';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

import { connect } from 'react-redux';
@connect(state => {
  return {
    people: peopleSelector(state),
    content: contentSelector(state),
    articles: articlesSelector(state),
    news: newsSelector(state),
    downloads: downloadsSelector(state),
    currentSort: currentListSortSelector(state),
    currentOrder: currentListOrderSelector(state)
  };
})

@injectIntl
export class TableContainer extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    dispatch: PropTypes.func.isRequired,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired,
    content: PropTypes.string.isRequired,
    people: PropTypes.object.isRequired,
    articles: PropTypes.object,
    news: PropTypes.object,
    downloads: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      order: '',
      sort: ''
    };
  }

  sortTable(param, order) {
    this.props.dispatch(applyParams({ sort: param, order }));
  }

  render() {
    const { content, people, currentSort, currentOrder } = this.props;
    const elements = this.props[content];

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              title="ID"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="person"
              title="Author"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="date_created"
              title="Created"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="date_updated"
              title="Updated"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="status"
              title="Status"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th title="Labels" visible/>
          <Th sort="title"
              title="Title"
              visible
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
        </tr>
        </thead>
        <tbody>
        {elements.map((element, index) =>
            <tr key={index}>
              <TdId visible>
                {element.id}
              </TdId>
              <Td visible>
                <PersonInTable person={people.get(element.person)}/>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_created}/></div>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_updated}/></div>
              </Td>
              <Td visible>
                {element.status}
              </Td>
              <Td visible>
                {element.labels && element.labels.join(', ')}
              </Td>
              <Td className="item-title">
                <a href="#"><SlicedString string={element.title}/></a>
              </Td>
            </tr>
        )
        }
        </tbody>
      </Table>
    );
  }
}