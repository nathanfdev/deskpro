import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import TimeAgo from '@deskpro/react-timeago';
import { connect } from 'react-redux';
import { Checkbox, Modal, Icon } from '@deskpro/react-components';
import AgentAvatar from 'DeskPRO/Component/Avatar/AgentAvatar';
import * as actions from '../Actions/snippetsActions';

@connect()
export class UsageHistoryModal extends React.Component {
  static propTypes = {
    snippet:    PropTypes.object,
    closeModal: PropTypes.func,
    dispatch:   PropTypes.func,
  };

  static getRating(use) {
    if (use.rating === 1) {
      return <Icon name="smile-o" className="positive" />;
    }
    if (use.rating === 0) {
      return <Icon name="meh-o" className="neutral" />;
    }
    if (use.rating === -1) {
      return <Icon name="frown-o" className="negative" />;
    }
    return null;
  }

  constructor(props) {
    super(props);
    this.state = {
      onlyWithFeedback: false,
      loading:          true,
      uses:             [],
      tickets:          [],
      persons:          [],
    };
  }

  componentWillMount() {
    this.props.dispatch(actions.getUsageLog(this.props.snippet.get('id')))
      .success(({ data, linked }) => {
        this.setState({
          loading: false,
          uses:    data,
          tickets: linked.ticket,
          persons: linked.person,
        });
      });
  }

  getUses = () => this.state.uses
    .filter(use => use.rating !== null || !this.state.onlyWithFeedback
    )
    .map((use) => {
      const ticket = this.state.tickets[use.ticket_message.ticket];
      const person = this.state.persons[ticket.person];
      return (<div key={use.id} className="use">
        <span className="ticket-id">#{ticket.id}</span>
        <span className="subject">{ticket.subject}</span>
        <span className="separator">|</span> <span className="person">{person.display_name}</span>
        <span className="email">&lt;{person.primary_email}&gt;</span>
        <AgentAvatar agent={use.person} />
        <span className="date"><TimeAgo date={use.date_created} /></span>
        {use.rating !== null ?
          <div className="rating">
            {UsageHistoryModal.getRating(use)}
            {use.message ?
              <span className="comment">{use.message}</span>
              : <FormattedMessage className="no-comment" id="agent.snippets.no_comment" />}
          </div>
          : null}
      </div>);
    }
  );

  handleDisplayOption = (value) => {
    this.setState({
      onlyWithFeedback: value,
    });
  };

  render() {
    const { snippet, closeModal } = this.props;
    return (
      <div id="usage_history_modal">
        <Modal
          title={<div>
            <FormattedMessage id="agent.snippets.usage_history" />: {snippet.get('title')}
          </div>}
          closeModal={closeModal}
        >
          <div className="display-options">
            <FormattedMessage id="agent.general.display_options" />:
            <Checkbox
              checked={this.state.onlyWithFeedback}
              value="onlyWithFeedback"
              onChange={this.handleDisplayOption}
            >
              <FormattedMessage id="agent.snippets.show_only_with_feedback" />
            </Checkbox>
          </div>
          {this.state.loading ?
            <div className="ui active inverted dimmer">
              <div className="ui text loader"><FormattedMessage id="agent.general.loading_dot" /></div>
            </div>
          : this.getUses()
          }
        </Modal>
      </div>
    );
  }
}
