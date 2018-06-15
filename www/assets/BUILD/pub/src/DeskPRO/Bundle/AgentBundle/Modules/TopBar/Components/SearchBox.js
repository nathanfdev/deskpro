import React from 'react';
import TokenField from '@deskpro/token-field';
import SemanticSearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { injectIntl, FormattedMessage } from 'react-intl';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';

@injectIntl
@connect(state => ({
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state),
  agents:            agentsSelector(state),
}))
class SearchBox extends React.Component {
  static propTypes = {
    intl:              PropTypes.object,
    ticketDepartments: PropTypes.object,
    agents:            PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      value: [],
    };
  }

  getTokenTypes = () => [
    {
      id:     'status',
      label:  this.props.intl.formatMessage({ id: 'agent.general.status' }).toLowerCase(),
      widget: 'SelectInput',
      props:  {
        dataSource: {
          getOptions: [
            { label: this.props.intl.formatMessage({ id: 'agent.tickets.status_awaiting_agent' }), value: 'awaiting_agent' },
            { label: this.props.intl.formatMessage({ id: 'agent.tickets.status_awaiting_user' }), value: 'awaiting_user' },
            { label: this.props.intl.formatMessage({ id: 'agent.tickets.status_resolved' }), value: 'resolved' },
            { label: this.props.intl.formatMessage({ id: 'agent.tickets.status_archived' }), value: 'archived' },
          ],
        },
        renderHeader: <h3><FormattedMessage id="agent.general.status" /></h3>,
        showSearch:   false
      },
      description: 'Status of the ticket'
    },
    {
      id:     'department',
      label:  this.props.intl.formatMessage({ id: 'agent.general.department' }).toLowerCase(),
      widget: 'SelectInput',
      props:  {
        dataSource: {
          getOptions: this.props.ticketDepartments.toArray()
                        .sort((a, b) => a.get('title') > b.get('title'))
                        .map(e => ({
                          label: e.get('title'),
                          value: e.get('id'),
                        }))
        },
      },
      showSearch: true,
    },
    {
      id:     'agent',
      label:  this.props.intl.formatMessage({ id: 'agent.general.agent' }).toLowerCase(),
      widget: 'SelectInput',
      props:  {
        dataSource: {
          getOptions: this.props.agents.toArray()
                        .sort((a, b) => a.get('name') > b.get('name'))
                        .map(e => ({
                          label: e.get('name'),
                          value: e.get('id'),
                        }))
        },
      },
      showSearch: true,
    },
    {
      id:          'date-ticket-created',
      widget:      'DateTimeInput',
      props:       {},
      description: 'When the ticket was created'
    },
    {
      id:          'date-ticket-resolved',
      widget:      'DateTimeInput',
      props:       {},
      description: 'When the ticket was resolved'
    },
    {
      id:          'user-message',
      widget:      'TextInput',
      props:       {},
      description: 'Message entered initially by the user'
    },
    {
      id:     'attach-size',
      widget: 'NumericRangeInput',
      props:  {
        unitPhrase:       'MB',
        convertFromValue: value => Math.round(value / 1024 / 1024),
        convertToValue:   value => value * 1024 * 1024,
      }
    },
    {
      id:          'user-waiting',
      widget:      'DurationInput',
      props:       {},
      description: 'Time waited by user'
    }];

  handleChange = (value) => {
    this.setState({
      value
    });
  };

  render() {
    const { ...props } = this.props;
    const { value } = this.state;
    return (
      <SemanticSearchBox {...props}>
        <TokenField
          tokenTypes={this.getTokenTypes()}
          value={value}
          onChange={this.handleChange}
          placeholder=""
        />
      </SemanticSearchBox>
    );
  }
}

export default SearchBox;
