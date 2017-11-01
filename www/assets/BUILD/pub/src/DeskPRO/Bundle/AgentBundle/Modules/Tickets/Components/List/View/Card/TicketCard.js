import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardLabel,
  CardStatusBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class TicketCard extends Component {
  static propTypes = {
    toggleSelected: PropTypes.func.isRequired,
    intl:           intlShape.isRequired,
    fields:         PropTypes.object.isRequired,
    selected:       PropTypes.bool.isRequired,
    ticket:         PropTypes.object.isRequired,
    agent:          PropTypes.object
  };

  renderStatus = ticket => {
    let status = ticket.get('status');
    if (ticket.get('hidden_status')) {
      status = `${status} (${ticket.get('hidden_status')})`;
    }
    status = status.replace(/_/g, ' ');

    return (
      <div style={{ textTransform: 'capitalize' }}>{status}</div>
    );
  };

  renderPerson = ticket => {
    if (this.props.fields.includes('person')) {
      const email = ticket.get('person_email');
      return (
        <CardLine>
          <div className="dpwd--card-line-item">
            <i className="fa fa-user" /> John Doe {email ? `<${email}>` : ''}
          </div>
        </CardLine>
      );
    }
    return null;
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { ticket } = this.props;

    switch (field.get('id')) {

      case 'id':
        return (
          <span key={field.get('id')}>
            <CardDisc />
            <CardLineItem>ID: {ticket.get('id')}</CardLineItem>
          </span>
        );

      case 'date_created':
        return (
          <CardLineItem key={field.get('id')}>
            <CardDisc />
            Created: <FormattedRelative value={ticket.get('date_created')} />
          </CardLineItem>
        );

      case 'urgency':
        return (
          <span key={field.get('id')}>
            <CardDisc />
            <CardLineItem icon="fa-book">Urgency: {ticket.get('urgency')}</CardLineItem>
          </span>
        );

      case 'labels':
        const labels = ticket.get('labels');
        return labels ? (
          <CardLine>
            <CardLineItem>
              <i className="fa fa-tags"></i>
              {labels.map((label, index) => <CardLabel key={index} label={label} />)}
            </CardLineItem>
          </CardLine>
        ) : null;

      case 'agent':
        return (
          <div className="dpwd--card-line-item">
            <i className="fa fa-user" /> {this.props.agent.get('name')}
          </div>
        );

      default:
        return null;
    }
  }

  render() {
    const { selected, ticket, toggleSelected, fields } = this.props;
    const handleClick = () => {
      toggleSelected(ticket.get('id'));
    };

    this._fields = [];
    let labels;
    fields.map(field => {
      if (field.get('id') === 'labels') {
        labels = field;
      } else {
        this._fields.push(field);
      }
    });

    return (
      <Card type="feedback" width={450}>
        <CardStatusBar align="left" level="5" />
        <CardStatusBar align="right" level="5" />

        <CardCheckbox selected={selected} onClick={handleClick} />

        <CardLine>
          <CardLineLeft>
            <CardTitle content={ticket.get('subject')} />
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>{this.renderStatus(ticket)}</CardLineItem>
          </CardLineRight>
        </CardLine>

        {this.renderPerson(ticket)}

        <CardLine>
          {this._fields.map(field => this.renderField(field))}
        </CardLine>

        {this.renderField(labels)}
      </Card>
    );
  }

}
