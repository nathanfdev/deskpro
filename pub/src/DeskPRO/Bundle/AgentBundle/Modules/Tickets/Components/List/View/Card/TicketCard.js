import React, { Component, PropTypes } from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox,
         CardDisc, CardTitle, CardLabel, CardStatusBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';

import { LegacyLinkBlock } from 'DeskPRO/Bundle/AgentBundle/Modules/Legacy/Components/legacyRoutes';

@injectIntl
export class TicketCard extends Component {
  static propTypes = {
    toggleSelected: PropTypes.func.isRequired,
    intl: intlShape.isRequired,
    fields: PropTypes.object.isRequired,
    selected: PropTypes.bool.isRequired,
    ticket: PropTypes.object.isRequired
  };

  renderStatus(ticket) {
    let status = ticket.get('status');
    if (ticket.get('hidden_status')) {
      status = `${status} (${ticket.get('hidden_status')})`;
    }
    status = status.replace(/\_/g, ' ');

    return (
      <div style={{textTransform: 'capitalize'}}>{status}</div>
    );
  }

  renderAgent() {
    return (
      <div className="dpwd--card-line-item">
        <i className="fa fa-user"></i> Admin Admin
      </div>
    );
  }

  renderPerson(ticket) {
    if (this.props.fields.includes('person')) {
      const email = ticket.get('person_email');
      return (
        <CardLine>
          <div className="dpwd--card-line-item">
            <i className="fa fa-user"></i> John Doe {email ? '<' + email + '>' : ''}
          </div>
        </CardLine>
      );
    }
  }

  renderId(ticket) {
    if (this.props.fields.includes('id')) {
      return (
        <span>
        <CardDisc/>
        <CardLineItem>ID: {ticket.get('id')}</CardLineItem>
      </span>
      );
    }
  }

  renderDateCreated(ticket) {
    if (this.props.fields.includes('date_created')) {
      return (
        <CardLineItem><CardDisc/>Created: <FormattedRelative value={ticket.get('date_created')}/></CardLineItem>
      );
    }
  }

  renderUrgency(ticket) {
    if (this.props.fields.includes('urgency')) {
      return (
        <span>
          <CardDisc/>
          <CardLineItem icon="fa-book">Urgency: {ticket.get('urgency')}</CardLineItem>
        </span>
      );
    }
  }

  renderLabels(ticket) {
    if (this.props.fields.includes('labels')) {
      const labels = ticket.get('labels');
      if (labels) {
        return (
          <CardLine>
            <CardLineItem>
              <i className="fa fa-tags"></i>
              {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
            </CardLineItem>
          </CardLine>
        );
      }
    }
  }

  render() {
    const { selected, ticket, toggleSelected } = this.props;

    return (
      <LegacyLinkBlock route={'/tickets/' + ticket.get('id')}>
        <Card type="feedback" width={450}>
          <CardStatusBar align="left" level="5"/>
          <CardStatusBar align="right" level="5"/>

          <CardCheckbox selected={selected} onClick={toggleSelected}/>

          <CardLine>
            <CardLineLeft>
              <CardTitle content={ticket.get('subject')}/>
            </CardLineLeft>

            <CardLineRight>
              <CardLineItem>{this.renderStatus(ticket)}</CardLineItem>
            </CardLineRight>
          </CardLine>

          {this.renderPerson(ticket)}
          <CardLine>
            {this.renderAgent(ticket)}
            {this.renderId(ticket)}
            {this.renderUrgency(ticket)}
            {this.renderDateCreated(ticket)}
          </CardLine>
          {this.renderLabels(ticket)}
        </Card>
      </LegacyLinkBlock>
    );
  }

}
