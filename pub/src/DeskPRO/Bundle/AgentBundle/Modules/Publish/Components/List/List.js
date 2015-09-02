import React from 'react';
import { SectionsPane, Section, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

export class List extends React.Component {
  render() {
    const { elements, view, toggleView } = this.props;

    return (
      <ListFrame>
        <SectionsPane>
          <Section>
            <div className="tickets-control-bar">

              <div className="bulk-edit-control">
                <a href="#">
                        <span className="checkbox">
                            <i className="fa fa-check"/>
                        </span>
                </a>
                <span className="count" style={{display: "none"}}><span>X</span></span>
              </div>

                <span className="ticket-controls-default">
                    Toggle: <a href onClick={toggleView}>{view}</a>
                </span>
            </div>
          </Section>
          <Section>
            {this.renderElements(view, elements)}
          </Section>
        </SectionsPane>
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
