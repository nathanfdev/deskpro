import React from 'react';
import TokenField from '@deskpro/token-field';
import SemanticSearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { injectIntl, FormattedMessage } from 'react-intl';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import * as searchActions from '../Actions/searchActions';

@injectIntl
@connect(state => ({
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state),
  agents:            agentsSelector(state),
  agentTeams:        allSelectorFactory('AgentTeam')(state),
  brands:            allSelectorFactory('Brand')(state),
}))
class SearchBox extends React.Component {
  static propTypes = {
    intl:              PropTypes.object,
    ticketDepartments: PropTypes.object,
    agents:            PropTypes.object,
    agentTeams:        PropTypes.object,
    brands:            PropTypes.object,
    dispatch:          PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      value:      [],
      tokenTypes: [],
    };
  }

  componentWillMount = () => {
    if (window.DP_HAS_NEW_SEARCH) {
      this.initTokenTypes();
    }
  };

  initTokenTypes = () => {
    const tokenTypes = [
      {
        id:     'status',
        label:  this.props.intl.formatMessage({ id: 'agent.general.status' }).toLowerCase().replace(/ /, '-'),
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
        label:  this.props.intl.formatMessage({ id: 'agent.general.department' }).toLowerCase().replace(/ /, '-'),
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
          showSearch: true,
        },
      },
      {
        id:     'agent',
        label:  this.props.intl.formatMessage({ id: 'agent.general.agent' }).toLowerCase().replace(/ /, '-'),
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
          showSearch: true,
        },
        showOnFocus: true,
      },
      {
        id:     'agent_team',
        label:  this.props.intl.formatMessage({ id: 'agent.general.agent_team' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: this.props.agentTeams.toArray()
                          .sort((a, b) => a.get('name') > b.get('name'))
                          .map(e => ({
                            label: e.get('name'),
                            value: e.get('id'),
                          }))
          },
          showSearch: true,
        },
      },
      {
        id:     'ticket_product',
        label:  this.props.intl.formatMessage({ id: 'agent.general.product' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadProducts({ search }))
          },
          showSearch: true,
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_category',
        label:  this.props.intl.formatMessage({ id: 'agent.general.category' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadCategories({ search }))
          },
          showSearch: true,
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_priority',
        label:  this.props.intl.formatMessage({ id: 'agent.general.priority' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPriorities({ search }))
          },
          showSearch: true,
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'urgency',
        label:  this.props.intl.formatMessage({ id: 'agent.general.urgency' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: Array.from(Array(9).keys())
                          .map(e => ({
                            label: e + 1,
                            value: e + 1,
                          }))
          },
          showSearch: false,
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_workflow',
        label:  this.props.intl.formatMessage({ id: 'agent.general.workflow' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadWorkflows({ search }))
          },
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'subject',
        label:  this.props.intl.formatMessage({ id: 'agent.general.subject' }).toLowerCase().replace(/ /, '-'),
        widget: 'TextInput',
        props:  {},
      },
      {
        id:     'date_created',
        label:  this.props.intl.formatMessage({ id: 'agent.general.date_created' }).toLowerCase().replace(/ /, '-'),
        widget: 'DateTimeInput',
        props:  {},
      },
      {
        id:     'date_resolved',
        label:  this.props.intl.formatMessage({ id: 'agent.general.date_resolved' }).toLowerCase().replace(/ /, '-'),
        widget: 'DateTimeInput',
        props:  {},
      },
      {
        id:       'last_agent_reply',
        label:    'last-agent-reply',
        widget:   'DateTimeInput',
        props:    {},
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:       'last_user_reply',
        label:    'last-user-reply',
        widget:   'DateTimeInput',
        props:    {},
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:          'user_waiting',
        label:       'user-waiting',
        widget:      'DurationInput',
        props:       {},
        description: 'Time waited by user',
        category:    this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'person_name',
        label:  this.props.intl.formatMessage({ id: 'agent.general.name' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPersons({ search }))
          },
          showSearch: true,
        },
      },
      {
        id:     'email_address',
        label:  this.props.intl.formatMessage({ id: 'agent.general.email_address' }).toLowerCase().replace(/ /, '-'),
        widget: 'TextInput',
        props:  {},
      },
      {
        id:     'followers',
        label:  this.props.intl.formatMessage({ id: 'agent.general.followers' }).toLowerCase().replace(/ /, '-'),
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
          isMultiple: true,
          showSearch: true,
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'organization',
        label:  this.props.intl.formatMessage({ id: 'agent.general.organization' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadOrganizations({ search }))
          },
          showSearch: true,
        },
      },
      {
        id:     'sla',
        label:  this.props.intl.formatMessage({ id: 'agent.general.sla' }).toLowerCase().replace(/ /, '-'),
        widget: 'BooleanInput',
        props:  {
          translations: {
            true:  this.props.intl.formatMessage({ id: 'agent.general.yes' }),
            false: this.props.intl.formatMessage({ id: 'agent.general.no' }),
          }
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'sla_status',
        label:  this.props.intl.formatMessage({ id: 'agent.general.sla_status' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadSlas({ search }))
          },
        },
        category: this.props.intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'brand',
        label:  this.props.intl.formatMessage({ id: 'agent.general.brand' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: this.props.brands.toArray()
                          .sort((a, b) => a.get('name') > b.get('name'))
                          .map(e => ({
                            label: e.get('name'),
                            value: e.get('id'),
                          }))
          },
          showSearch: true,
        },
      },
      {
        id:     'id',
        label:  this.props.intl.formatMessage({ id: 'agent.general.id' }).toLowerCase().replace(/ /, '-'),
        widget: 'TextInput',
        props:  {},
      },
      {
        id:     'file_name',
        label:  this.props.intl.formatMessage({ id: 'agent.general.file' }).toLowerCase().replace(/ /, '-'),
        widget: 'TextInput',
        props:  {},
      },
      {
        id:     'file_size',
        label:  this.props.intl.formatMessage({ id: 'agent.general.filesize' }).toLowerCase().replace(/ /, '-'),
        widget: 'NumericRangeInput',
        props:  {},
      },
      {
        id:     'filetype',
        label:  this.props.intl.formatMessage({ id: 'agent.general.filetype' }).toLowerCase().replace(/ /, '-'),
        widget: 'TextInput',
        props:  {},
      },
    ];
    this.setState(tokenTypes);
    this.props.dispatch(searchActions.loadCustomFields()).then((res) => {
      const customFields = res.map(field => ({
        id:          `custom_field_${field.id}`,
        label:       `${this.props.intl.formatMessage({ id: 'agent.general.field' }).toLowerCase()}-${field.title.toLowerCase().replace(/ /, '-')}`,
        widget:      this.tokenFromField(field),
        props:       this.tokenPropsFromField(field),
        description: field.description,
      }));
      tokenTypes.concat(customFields);
      this.setState({
        tokenTypes: tokenTypes.concat(customFields)
      });
    });
  };

  tokenFromField = (field) => {
    switch (field.widget_type) {
      case 'choice':
      case 'multichoice':
      case 'radio':
      case 'checkbox':
        return 'SelectInput';
      case 'date':
      case 'datetime':
        return 'DateTimeInput';
      case 'text':
      case 'textarea':
      default:
        return 'TextInput';
    }
  };

  tokenPropsFromField = (field) => {
    const props = {};
    if (field.choices.length) {
      props.dataSource = {
        getOptions: field.choices
                      .sort((a, b) => a.title > b.title)
                      .map(e => ({
                        label: e.title,
                        value: e.id,
                      }))
      };
    }
    return props;
  };

  handleChange = (value) => {
    this.setState({
      value
    });
  };

  render() {
    const { ...props } = this.props;
    const { value, tokenTypes } = this.state;
    if (window.DP_HAS_NEW_SEARCH) {
      return (
        <SemanticSearchBox {...props}>
          <TokenField
            tokenTypes={tokenTypes}
            value={value}
            onChange={this.handleChange}
            placeholder=""
            zIndex={1800}
            showTokensOnFocus
          />
        </SemanticSearchBox>
      );
    }
    return <SemanticSearchBox {...props} />;
  }
}

export default SearchBox;
