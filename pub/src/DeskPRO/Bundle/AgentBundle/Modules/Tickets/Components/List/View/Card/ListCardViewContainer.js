import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { elementsSelector, cardVisibleFieldsSelector } from '../../../../Selectors/list';
import { TicketCard } from './TicketCard';
import { ticketsSelector } from '../../../../Selectors/recordStores';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';

@connect(state => ({
  ids: elementsSelector(state),
  tickets: ticketsSelector(state),
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
    const { dispatch } = this.props;
    dispatch(toggleSelectedAction(id));
  }

  renderCard(id) {
    const { tickets, fields, selected } = this.props;

    return (
      <TicketCard key={id}
                  fields={fields}
                  selected={selected.indexOf(id) > -1}
                  toggleSelected={this.toggleSelected.bind(this, id)}
                  ticket={tickets.get(id)}/>
    );
  }

  render() {
    const { ids } = this.props;

    return (
      <div>
        {ids.map(id => this.renderCard(id))}
      </div>
    );
  }
}