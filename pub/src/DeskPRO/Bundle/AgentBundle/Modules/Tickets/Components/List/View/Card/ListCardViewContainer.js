import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { elementsSelector, selectedSelector, cardVisibleFieldsSelector } from '../../../../Selectors/list';
import { TicketCardContainer } from './TicketCardContainer';

@connect(state => ({
  elements: elementsSelector(state),
  selected: selectedSelector(state),
  fields: cardVisibleFieldsSelector(state)
}))
export class ListCardViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.object.isRequired,
    fields: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { elements, fields, selected } = this.props;

    return (
      <div>
        {elements.map(ticket =>
          <TicketCardContainer
            key={ticket.get('id')}
            fields={fields}
            selected={selected.indexOf(ticket.get('id')) > -1}
            toggleSelected={() => this.props.dispatch(toggleSelected(ticket.get('id')))}
            ticket={ticket}
          />
        )}
      </div>
    );
  }
}