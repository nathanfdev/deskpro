import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  Table,
  Th,
  Td,
  TdId,
  TdTitle,
  TableCheckbox
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import {
  elementsSelector,
  listOrderBySelector,
  listOrderDirSelector,
  tableVisibleFieldsSelector
} from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { applyListParams } from '../../../../Actions/listActions';

@connect(state => ({
  ids:      elementsSelector(state),
  tickets:  collectionSelectorFactory('Ticket', 'list')(state),
  orderBy:  listOrderBySelector(state),
  orderDir: listOrderDirSelector(state),
  selected: selectedSelector(state),
  fields:   tableVisibleFieldsSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    ids:      PropTypes.object.isRequired,
    tickets:  PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    orderBy:  PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
    fields:   PropTypes.object.isRequired
  };

  onClick = (id) => {
    const { dispatch } = this.props;
    dispatch(toggleSelectedAction(id));
  };

  sortTable = (orderBy, orderDir) => {
    this.props.dispatch(applyListParams({ order_by: orderBy, order_dir: orderDir }));
  };

  isVisible = field => this.props.fields.includes(field);

  renderHeader() {
    return (
      <tr>
        <Th />
        <Th
          sort="id" title="ID"
          visible={this.isVisible('id')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="urgency" title="Urgency"
          visible={this.isVisible('urgency')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="person" title="Person"
          visible={this.isVisible('person')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="person_email" title="Person email"
          visible={this.isVisible('person_email')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="agent" title="Agent"
          visible={this.isVisible('agent')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="subject" title="Subject"
          visible={this.isVisible('subject')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="status" title="Status"
          visible={this.isVisible('status')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="date_created" title="Created"
          visible={this.isVisible('date_created')}
          order={false}
          onChange={this.sortTable}
        />
        <Th
          sort="labels" title="Labels"
          visible={this.isVisible('labels')}
          order={false}
          onChange={this.sortTable}
        />
      </tr>
    );
  }

  render() {
    const { ids, tickets, selected } = this.props;
    return (
      <Table>
        <thead>
        {this.renderHeader()}
        </thead>
        <tbody>
        {ids.map(id =>
                   <TicketRow
                     key={id}
                     ticket={tickets.get(id)}
                     isSelected={selected.includes(id)}
                     onClick={this.onClick}
                     isVisible={this.isVisible}
                   />
        )}
        </tbody>
      </Table>
    );
  }

}

export class TicketRow extends Component {
  static propTypes = {
    ticket:     PropTypes.object.isRequired,
    onClick:    PropTypes.func.isRequired,
    isVisible:  PropTypes.func.isRequired,
    isSelected: PropTypes.bool.isRequired
  };

  handleClick = () => {
    const { ticket, onClick } = this.props;
    onClick(ticket.get('id'));
  };

  render() {
    const { ticket, isSelected, isVisible } = this.props;
    const id = ticket.get('id');

    return (
      <tr>
        <Td><TableCheckbox selected={isSelected} onClick={this.handleClick} /></Td>
        <TdId visible={isVisible('id')}>{id}</TdId>
        <Td visible={isVisible('urgency')}>{ticket.get('urgency')}</Td>
        <Td visible={isVisible('person')}>John Doe</Td>
        <Td visible={isVisible('person_email')}>{ticket.get('person_email')}</Td>
        <Td visible={isVisible('agent')}>Admin Admin</Td>
        <TdTitle visible={isVisible('subject')}>{ticket.get('subject')}</TdTitle>
        <TdTitle visible={isVisible('status')}>{ticket.get('status')}</TdTitle>
        <TdTitle visible={isVisible('date_created')}>{ticket.get('date_created')}</TdTitle>
        <TdTitle visible={isVisible('labels')}>{ticket.get('labels')}</TdTitle>
      </tr>
    );
  }
}
