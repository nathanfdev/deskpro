import React, { Component, PropTypes } from 'react';
import { TableView, TableHeader, Th, TableBody, Row, Td, IdContainer, PersonInTable, TableCheckbox }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { connect } from 'react-redux';
import { elementsSelector, selectedSelector } from '../../../../Selectors/list';
import { toggleSelected } from '../../../../Actions/listActions';

@connect(state => ({
  elements: elementsSelector(state),
  selected: selectedSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  sortTable(param, order) {
    console.log('Sorting table...', param, order);
  }

  render() {
    return (
      <TableView>
        <TableHeader>
          {this.renderHeader()}
        </TableHeader>
        <TableBody>
          {this.props.elements.map(ticket => this.renderRow(ticket))}
        </TableBody>
      </TableView>
    );
  }

  renderRow(ticket) {
    const id = ticket.get('id');
    const selected = this.props.selected.includes(id);
    const onClick = (elementId) => () => {
      this.props.dispatch(toggleSelected(elementId));
    };

    return (
      <Row key={id}>
        <Td><TableCheckbox selected={selected} onClick={onClick(id)} /></Td>
        <Td className="id-col"><IdContainer id={id}/></Td>
        <Td>{ticket.get('urgency')}</Td>
        <Td>Admin Admin</Td>
        <Td className="item-title">{ticket.get('subject')}</Td>
        <Td>John Doe</Td>
      </Row>
    );
  }

  renderHeader() {
    return (
      <tr>
        <Th />
        <Th value="id" label="ID" className="sortable"
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="urgency" label="Urgency" className="sortable"
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="agent" label="Agent"
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="subject" label="Subject"
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="person" label="Person"
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
      </tr>
    );
  }
}