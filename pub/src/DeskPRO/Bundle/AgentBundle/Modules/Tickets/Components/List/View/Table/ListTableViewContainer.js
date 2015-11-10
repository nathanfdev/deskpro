import React, { Component, PropTypes } from 'react';
import { TableView, TableHeader, Th, TableBody, Row, Td, IdContainer, PersonInTable, TableCheckbox }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { connect } from 'react-redux';
import { elementsSelector, selectedSelector, tableVisibleFieldsSelector } from '../../../../Selectors/list';
import { toggleSelected } from '../../../../Actions/listActions';

@connect(state => ({
  elements: elementsSelector(state),
  selected: selectedSelector(state),
  fields: tableVisibleFieldsSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    fields: PropTypes.object.isRequired
  };

  sortTable(param, order) {
    console.log('Sorting table...', param, order);
  }

  isVisible(field) {
    return this.props.fields.includes(field);
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
        <Td visible={this.isVisible('id')} className="id-col"><IdContainer id={id}/></Td>
        <Td visible={this.isVisible('urgency')}>{ticket.get('urgency')}</Td>
        <Td visible={this.isVisible('person')}>John Doe</Td>
        <Td visible={this.isVisible('person_email')}>{ticket.get('person_email')}</Td>
        <Td visible={this.isVisible('agent')}>Admin Admin</Td>
        <Td visible={this.isVisible('subject')} className="item-title">{ticket.get('subject')}</Td>
        <Td visible={this.isVisible('status')} className="item-title">{ticket.get('status')}</Td>
        <Td visible={this.isVisible('date_created')} className="item-title">{ticket.get('date_created')}</Td>
        <Td visible={this.isVisible('labels')} className="item-title">{ticket.get('labels')}</Td>
      </Row>
    );
  }

  renderHeader() {
    return (
      <tr>
        <Th />
        <Th value="id" label="ID" className="sortable"
            visible={this.isVisible('id')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="urgency" label="Urgency" className="sortable"
            visible={this.isVisible('urgency')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="person" label="Person"
            visible={this.isVisible('person')}
            order={false}
            sortTable={this.sortTable.bind(this)}
          />
        <Th value="person_email" label="Person email"
            visible={this.isVisible('person_email')}
            order={false}
            sortTable={this.sortTable.bind(this)}
          />
        <Th value="agent" label="Agent"
            visible={this.isVisible('agent')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="subject" label="Subject"
            visible={this.isVisible('subject')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="status" label="Status"
            visible={this.isVisible('status')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="date_created" label="Created"
            visible={this.isVisible('date_created')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
        <Th value="labels" label="Labels"
            visible={this.isVisible('labels')}
            order={false}
            sortTable={this.sortTable.bind(this)}
        />
      </tr>
    );
  }
}