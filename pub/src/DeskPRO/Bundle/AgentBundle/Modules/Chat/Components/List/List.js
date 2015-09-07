import React from 'react';
import { SectionsPane, Section, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame, ControlBar, OrderBy, ListTableViewSwitcher  }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

export class List extends React.Component {
  render() {
    const {
      elements, viewMode, sort, sortOptions, displayFields,
      toggleView, toggleOrder, showSortChoice, toggleSort
      } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortOptions={sortOptions}
                   toggleSort={toggleSort.bind(this)}
                   showSortChoice={showSortChoice.bind(this)}
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
    let key = 0;

    return (
      <div>
        <h1>List View</h1>
        {elements.map(e => <div key={key++} style={{marginTop:'20px'}}>List item: {e}</div>)}
      </div>
    );
  }

  renderTableView(elements) {
    let key = 0;

    return (
      <div>
        <h1>Table View</h1>
        {elements.map(e => <div key={key++} style={{marginTop:'20px'}}>Table row: {e}</div>)}
      </div>
    );
  }
}
