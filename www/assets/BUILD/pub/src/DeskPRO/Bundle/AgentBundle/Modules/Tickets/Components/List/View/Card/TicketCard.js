import React, { Component, PropTypes } from 'react';
import {
  Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox,
  CardDisc, CardTitle, CardLabel, CardStatusBar
}
  from '../../../../../Common/Components/ListFrame/View/Card';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';

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

  renderAgent = () =>
    <div className="dpwd--card-line-item">
      <i className="fa fa-user" /> {this.props.agent.get('name')}
    </div>;

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

  renderId = id =>
    <span>
      <CardDisc />
      <CardLineItem>ID: {id}</CardLineItem>
    </span>;

  renderDateCreated = date =>
    <CardLineItem>
      <CardDisc />
      Created: <FormattedRelative value={date} />
    </CardLineItem>;

  renderUrgency = urgency =>
    <span>
      <CardDisc />
      <CardLineItem icon="fa-book">Urgency: {urgency}</CardLineItem>
    </span>;

  renderLabels = labels => {
    if (labels) {
      return (
        <CardLine>
          <CardLineItem>
            <i className="fa fa-tags" />
            {labels.map((label, index) => <CardLabel key={index} label={label} />)}
          </CardLineItem>
        </CardLine>
      );
    }
    return null;
  };

  render() {
    const { selected, ticket, toggleSelected, agent, fields } = this.props;
    const handleClick = () => {
      toggleSelected(ticket.get('id'));
    };

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
          {agent && this.renderAgent()}
          {fields.includes('id') && this.renderId(ticket.get('id'))}
          {fields.includes('urgency') && this.renderUrgency(ticket.get('urgency'))}
          {fields.includes('date_created') && this.renderDateCreated(ticket.get('date_created'))}
        </CardLine>
        {fields.includes('labels') && this.renderLabels(ticket.get('labels'))}
      </Card>
    );
  }

}
