import React, { Component, PropTypes } from 'react';
import { Table, Th, Td, TdId, TdTitle, TableCheckbox }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { connect } from 'react-redux';
import { elementsSelector, tableVisibleFieldsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { toggleSelected } from '../../../../Actions/listActions';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

// @todo Extract Row component (to omit selecting all tickets from record store + better structure + easier to test)

@connect(state => ({
  ids:      elementsSelector(state),
  tickets:  collectionSelectorFactory('Ticket', 'list')(state),
  selected: selectedSelector(state),
  fields:   tableVisibleFieldsSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    ids:      PropTypes.array.isRequired,
    tickets:  PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    fields:   PropTypes.object.isRequired
  };

  sortTable = (param, order) => {
    console.log('Sorting table...', param, order);
  };

  isVisible = field => this.props.fields.includes(field);

  renderRow = id => {
    const { tickets, selected, dispatch } = this.props;
    const ticket = tickets.get(id);

    const isSelected = selected.includes(id);
    const onClick    = () => {
      dispatch(toggleSelected(id));
    };

    return (
      <tr key={id}>
        <Td><TableCheckbox selected={isSelected} onClick={onClick} /></Td>
        <TdId visible={this.isVisible('id')}>{id}</TdId>
        <Td visible={this.isVisible('urgency')}>{ticket.get('urgency')}</Td>
        <Td visible={this.isVisible('person')}>John Doe</Td>
        <Td visible={this.isVisible('person_email')}>{ticket.get('person_email')}</Td>
        <Td visible={this.isVisible('agent')}>Admin Admin</Td>
        <TdTitle visible={this.isVisible('subject')}>{ticket.get('subject')}</TdTitle>
        <TdTitle visible={this.isVisible('status')}>{ticket.get('status')}</TdTitle>
        <TdTitle visible={this.isVisible('date_created')}>{ticket.get('date_created')}</TdTitle>
        <TdTitle visible={this.isVisible('labels')}>{ticket.get('labels')}</TdTitle>
      </tr>
    );
  };

  renderHeader() {
    return (
      <tr>
        <Th />
        <Th sort="id" title="ID"
            visible={this.isVisible('id')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="urgency" title="Urgency"
            visible={this.isVisible('urgency')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="person" title="Person"
            visible={this.isVisible('person')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="person_email" title="Person email"
            visible={this.isVisible('person_email')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="agent" title="Agent"
            visible={this.isVisible('agent')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="subject" title="Subject"
            visible={this.isVisible('subject')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="status" title="Status"
            visible={this.isVisible('status')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="date_created" title="Created"
            visible={this.isVisible('date_created')}
            order={false}
            onChange={this.sortTable} />
        <Th sort="labels" title="Labels"
            visible={this.isVisible('labels')}
            order={false}
            onChange={this.sortTable} />
      </tr>
    );
  }

  render() {
    return (
      <Table>
        <thead>
        {this.renderHeader()}
        </thead>
        <tbody>
        {this.props.ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }

}