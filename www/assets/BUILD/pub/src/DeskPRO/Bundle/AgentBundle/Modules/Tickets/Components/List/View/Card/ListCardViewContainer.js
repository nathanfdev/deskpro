import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { cardFieldsSelector } from '../../../../Selectors/list';
import { TicketCard } from './TicketCard';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';

@connect(state => ({
  tickets:  collectionSelectorFactory('Ticket', 'list')(state),
  agents:   collectionSelectorFactory('Person', 'agents')(state),
  selected: selectedSelector(state),
  fields:   cardFieldsSelector(state)
}))
export class ListCardViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    tickets:  PropTypes.object.isRequired,
    agents:   PropTypes.object.isRequired,
    fields:   PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  toggleSelected = (id) => {
    this.props.dispatch(toggleSelectedAction(id));
  };

  renderCard(ticket) {
    const { fields, selected, agents } = this.props;
    const id = ticket.get('id');
    const agent = agents.get(ticket.get('agent'));

    return (
      <TicketCard
        key={id}
        fields={fields}
        selected={selected.indexOf(id) > -1}
        toggleSelected={this.toggleSelected}
        ticket={ticket}
        agent={agent}
      />
    );
  }

  render() {
    return (
      <div>
        {this.props.tickets.entrySeq().map(([id, ticket]) => this.renderCard(ticket))}
      </div>
    );
  }
}
