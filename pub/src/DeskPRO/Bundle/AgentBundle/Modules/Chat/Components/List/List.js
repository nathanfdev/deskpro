import React from 'react';

import { ListFrame, ControlBar, OrderBy, ListTableViewSwitcher, TableView, TableBody  }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { ChatCard } from './ChatCard.js';

export class List extends React.Component {
  render() {
    const {
      elements, viewMode, sort, sortName, order, sortOptions, displayFields,
      toggleView, toggleOrder, toggleSort
      } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleSort={toggleSort.bind(this)}
                   toggleOrder={toggleOrder.bind(this)}
            />
          <ListTableViewSwitcher displayFields={displayFields} toggleView={toggleView.bind(this)} {...this.props}/>
        </ControlBar>

        {this.renderElements(viewMode, elements)}

      </ListFrame>
    );
  }

  renderElements(view, elements) {
    if (!elements.length) {
      return 'No data to display'
    }

    switch (view) {
      case 'list':
        return this.renderListView(elements);
      case 'table':
        return this.renderTableView(elements);
      default:
        throw `Unknown "${view}" view type`;
    }
  }

  renderListView(elements) {
    console.log('Elements:', elements);
    return (
      <div>
        <h1>List View</h1>
        {elements.map((element, index) => <ChatCard key={index} chat={element}/>)}
      </div>
    );
  }

  renderTableView(elements) {
    console.log('Elements:', elements);

    return (
      <div>
        <h1>Table View</h1>
        <TableView>
          <TableHeader/>
          <TableBody>
            {elements.map((element, index) => <Row key={index} element={element}/>)}
          </TableBody>
        </TableView>
      </div>
    );
  }
}
