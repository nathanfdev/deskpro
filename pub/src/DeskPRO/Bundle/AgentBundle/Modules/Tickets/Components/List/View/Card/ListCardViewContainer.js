import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { cardVisibleFieldsSelector } from '../../../../Selectors/list';
import { TicketCard } from './TicketCard';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore/index';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';

@connect(state => ({
  tickets: collectionSelectorFactory('Ticket', 'list')(state),
  selected: selectedSelector(state),
  fields: cardVisibleFieldsSelector(state)
}))
export class ListCardViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    ids: PropTypes.array.isRequired,
    tickets: PropTypes.object.isRequired,
    fields: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  toggleSelected(id, e) {
    e.stopPropagation();
    this.props.dispatch(toggleSelectedAction(id));
  }

  renderCard(ticket) {
    const { fields, selected } = this.props;
    const id = ticket.get('id');

    return (
      <TicketCard key={id}
                  fields={fields}
                  selected={selected.indexOf(id) > -1}
                  toggleSelected={this.toggleSelected.bind(this, id)}
                  ticket={ticket}/>
    );
  }

  render() {
    return (
      <div>
        {this.props.tickets.map(ticket => this.renderCard(ticket))}
      </div>
    );
  }
}
