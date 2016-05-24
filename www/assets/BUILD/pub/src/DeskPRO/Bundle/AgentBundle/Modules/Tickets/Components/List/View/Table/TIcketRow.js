import React, { Component, PropTypes } from 'react';
import {
  Table,
  Th,
  Td,
  TdId,
  TdTitle,
  TableCheckbox
} from '../../../../../Common/Components/ListFrame';

export class TicketRow extends Component {
  static propTypes = {
    ticket:     PropTypes.object.isRequired,
    onClick:    PropTypes.func.isRequired,
    isVisible:  PropTypes.func.isRequired,
    isSelected: PropTypes.bool.isRequired,
    fields:     PropTypes.object.isRequired
  };

  handleClick = () => {
    const { ticket, onClick } = this.props;
    onClick(ticket.get('id'));
  };

  renderField(field) {
    if (!field) {
      return null;
    }

    if (!field.get('visible')) {
      return null;
    }

    const { ticket } = this.props;
    const fieldId = field.get('id');

    switch (fieldId) {

      case 'id':
        return <TdId key={fieldId}>{ticket.get('id')}</TdId>

      case 'subject':
      case 'status':
      case 'date_created':
      case 'labels':
        return <TdTitle key={fieldId}>{ticket.get(fieldId)}</TdTitle>

      default:
        return <Td key={fieldId}>{ticket.get(fieldId)}</Td>
    }
  }

  render() {
    const { isSelected, fields } = this.props;

    return (
      <tr>
        <Td>
          <TableCheckbox selected={isSelected} onClick={this.handleClick} />
        </Td>
        {fields.map(field => this.renderField(field))}
      </tr>
    );
  }
}
