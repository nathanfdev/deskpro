import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Table, Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { selectedSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/massActions';
import { toggleSelectedAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/massActions';
import { listOrderBySelector, listOrderDirSelector, tableFieldsSelector } from '../../../../Selectors/list';
import { applyListParams } from '../../../../Actions/listActions';
import { TicketRow } from './TicketRow';

@connect(state => ({
  tickets:  collectionSelectorFactory('Ticket', 'list')(state),
  agents:   collectionSelectorFactory('Person', 'agents')(state),
  people:   collectionSelectorFactory('Person', 'tickets')(state),
  orderBy:  listOrderBySelector(state),
  orderDir: listOrderDirSelector(state),
  selected: selectedSelector(state),
  fields:   tableFieldsSelector(state)
}))
export class ListTableViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    tickets:  PropTypes.object.isRequired,
    agents:   PropTypes.object.isRequired,
    people:   PropTypes.object.isRequired,
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

  renderHeaderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { orderDir, orderBy } = this.props;

    return (
      <Th
        sort={field.get('id')}
        title={field.get('title')}
        orderDir={orderDir}
        orderBy={orderBy}
        onChange={this.sortTable}
      />
    );
  }

  render() {
    const { tickets, agents, people, selected, fields } = this.props;
    const selectedSet = selected.toSet();

    return (
      <Table>
        <thead>
        <tr>
          <Th />
          {fields.map(field => this.renderHeaderField(field))}
        </tr>
        </thead>
        <tbody>
        {tickets.entrySeq().map(
          ([id, ticket]) =>
            <TicketRow
              key={ticket.get('id')}
              ticket={ticket}
              isSelected={selectedSet.has(ticket.get('id'))}
              onClick={this.onClick}
              agent={agents.get(ticket.get('agent'))}
              person={people.get(ticket.get('person'))}
              fields={fields}
            />
        )}
        </tbody>
      </Table>
    );
  }

}

