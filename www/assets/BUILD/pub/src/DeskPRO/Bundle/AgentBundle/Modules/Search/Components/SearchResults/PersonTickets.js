import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import { Select, Input } from '@deskpro/react-components';
import fakeResults from 'tests/DemoState/AgentBundle/Modules/Search/result.json';
import { Ticket } from './Tickets';

@injectIntl
export default class PersonTickets extends React.Component {
  static propTypes = {
    intl:     PropTypes.object.isRequired,
    personId: PropTypes.number.isRequired,
  };

  getOptions()  {
    const { formatMessage } = this.props.intl;
    return [
      {
        value: 'urgency_asc',
        label: `${formatMessage({ id: 'agent.general.urgency' })} ${formatMessage({ id: 'agent.general.sort_asc' })}`
      },
      {
        value: 'urgency_desc',
        label: `${formatMessage({ id: 'agent.general.urgency' })} ${formatMessage({ id: 'agent.general.sort_desc' })}`
      },
    ];
  }

  renderTickets() {
    const { tickets } = fakeResults;
    return tickets.map(ticket =>
      <Ticket
        key={ticket.id}
        ticket={ticket}
        displayMessages={false}
        displayPerson={false}
      />
    );
  }

  render() {
    const { intl } = this.props;
    return (
      <div className="person-tickets">
        <div className="header">
          <FormattedMessage id="agent.general.sort_by" />
          <Select
            className="sort"
            options={this.getOptions()}
            searchable={false}
          />
          <Input
            className="search"
            icon="search"
            placeholder={intl.formatMessage({ id: 'agent.general.search' })}
          />
        </div>
        {this.renderTickets()}
      </div>
    );
  }
}
