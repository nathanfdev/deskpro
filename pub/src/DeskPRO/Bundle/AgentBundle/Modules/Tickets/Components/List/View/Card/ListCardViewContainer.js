import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { toggleSelected } from '../../../../Actions/listActions';
import { elementsSelector, selectedSelector, cardVisibleFieldsSelector } from '../../../../Selectors/list';
import { TicketCardContainer } from './TicketCardContainer';
import { ticketsSelector }
  from '../../../../Selectors/recordStores';

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

  renderCard(id) {
    const { tickets, fields, selected, dispatch } = this.props;

    return (
      <TicketCardContainer key={id}
                           fields={fields}
                           selected={selected.indexOf(id) > -1}
                           toggleSelected={() => dispatch(toggleSelected(id))}
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