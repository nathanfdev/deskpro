import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  Table,
  Th,
  Td,
  TdId,
  TdTitle,
  TableCheckbox
} from '../../../../../Common/Components/ListFrame';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { listOrderBySelector, listOrderDirSelector, tableVisibleFieldsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { applyListParams } from '../../../../Actions/listActions';

@connect(state => ({
  tickets:  collectionSelectorFactory('Ticket', 'list')(state),
  orderBy:  listOrderBySelector(state),
  orderDir: listOrderDirSelector(state),
  selected: selectedSelector(state),
  fields:   tableVisibleFieldsSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
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
    const { orderBy, orderDir } = this.props;

    return (
      <tr>
        <Th />
        <Th
          sort="id" title="ID"
          visible={this.isVisible('id')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="urgency" title="Urgency"
          visible={this.isVisible('urgency')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="person" title="Person"
          visible={this.isVisible('person')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="person_email" title="Person email"
          visible={this.isVisible('person_email')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="agent" title="Agent"
          visible={this.isVisible('agent')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="subject" title="Subject"
          visible={this.isVisible('subject')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="status" title="Status"
          visible={this.isVisible('status')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="date_created" title="Created"
          visible={this.isVisible('date_created')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
        <Th
          sort="labels" title="Labels"
          visible={this.isVisible('labels')}
          orderDir={orderDir}
          orderBy={orderBy}
          onChange={this.sortTable}
        />
      </tr>
    );
  }

  render() {
    const { tickets, selected } = this.props;
    return (
      <Table>
        <thead>
        {this.renderHeader()}
        </thead>
        <tbody>
        {tickets.map(
          ticket =>
            <TicketRow
              key={ticket.get('id')}
              ticket={ticket}
              isSelected={selected.includes(ticket.get('id'))}
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
