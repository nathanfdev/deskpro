import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import { Icon, Select } from '@deskpro/react-components';
import AgentAvatar from 'DeskPRO/Component/Avatar/AgentAvatar';

export class Ticket extends React.Component {
  static propTypes = {
    ticket:          PropTypes.object.isRequired,
    displayPerson:   PropTypes.bool,
    displayMessages: PropTypes.bool,
  };

  static defaultProps = {
    displayPerson:   true,
    displayMessages: true,
  };

  constructor(props) {
    super(props);
    this.state = {
      messagesExpanded: false,
    };
  }

  toggleMessages = () => {
    this.setState({
      messagesExpanded: !this.state.messagesExpanded
    });
  };

  renderPerson() {
    const { ticket, displayPerson } = this.props;
    if (displayPerson) {
      return [
        <span className="separator" key="separator" />,
        <span className="person-name" key="name">
          {ticket.person_name}
        </span>,
        <span className="person-email" key="email">
          {ticket.person_email}
        </span>
      ];
    }
    return null;
  }

  renderMessages() {
    const { displayMessages } = this.props;
    const { messages } = this.props.ticket;
    if (displayMessages && messages && messages.length) {
      if (messages.length > 1) {
        if (!this.state.messagesExpanded) {
          return (
            <div className="messages">
              <span className="messages-count" onClick={this.toggleMessages}>
                1 of {messages.length} <Icon name="caret-down" />
              </span> {messages[0].text}
            </div>
          );
        }
        return (
          <div className="messages">
            <table>
              <tbody>
                {messages.map((message, index) => (
                  <tr>
                    <td>
                      {index === 0 ?
                        <span className="messages-count" onClick={this.toggleMessages}>
                          <Icon name="caret-up" />
                        </span>
                        : null
                      }
                    </td>
                    <td className="message">
                      {message.text}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        );
      }
      return (
        <div className="messages">{messages[0].text}</div>
      );
    }
    return null;
  }

  renderUrgency() {
    const { ticket } = this.props;
    if (ticket.urgency) {
      return <span className={classNames('sla', `sla${ticket.urgency}`)}>{ticket.urgency}</span>;
    }
    return null;
  }


  render() {
    const { ticket } = this.props;
    return (
      <div className="ticket">
        <div className="ticket-info">
          <span className="id">{`#${ticket.id}`}</span>
          <span className="title">
            {ticket.subject}
          </span>
          {this.renderPerson()}
          {this.renderUrgency()}
          <AgentAvatar agent={ticket.agent} />
        </div>
        {this.renderMessages()}
      </div>
    );
  }
}

@injectIntl
export default class Tickets extends React.Component {
  static propTypes = {
    intl:    PropTypes.object.isRequired,
    tickets: PropTypes.array
  };

  constructor(props) {
    super(props);
    this.state = {
      sort: 'relevance',
    };
  }

  getOptions()  {
    const { formatMessage } = this.props.intl;
    return [
      {
        value: 'relevance',
        label: formatMessage({ id: 'agent.general.urgency' })
      },
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

  render() {
    const { tickets } = this.props;
    const { sort } = this.state;
    return (
      <section className="tickets">
        <header>
          <h1>Tickets</h1>
          <span className="count">{tickets.length}</span>
          <span className="sort-by">
            <FormattedMessage id="agent.general.sort_by" />
          </span>
          <Select
            className="sort"
            options={this.getOptions()}
            clearable={false}
            searchable={false}
            value={sort}
          />
          <a href="#"><FormattedMessage id="agent.search.manage_tickets" /></a>
        </header>
        {tickets.map(ticket =>
          <Ticket key={ticket.id} ticket={ticket} />
        )}
      </section>
    );
  }
}
