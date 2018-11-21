import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import TokenField from '@deskpro/token-field/dist/index';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import fakeResults from 'tests/DemoState/AgentBundle/Modules/Search/result.json';
import * as searchActions from '../Actions/searchActions';
import SearchResults from './SearchResults';

@connect(state => ({
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state),
  agents:            agentsSelector(state),
  agentTeams:        allSelectorFactory('AgentTeam')(state),
  brands:            allSelectorFactory('Brand')(state),
}))
class SearchContainer extends React.Component {
  static propTypes = {
    intl:              PropTypes.object,
    ticketDepartments: PropTypes.object,
    agents:            PropTypes.object,
    agentTeams:        PropTypes.object,
    brands:            PropTypes.object,
    dispatch:          PropTypes.func.isRequired,
    closeMenu:         PropTypes.func.isRequired,
    updateStyle:       PropTypes.func,
  };

  static defaultProps = {
    updateStyle() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      value:      [],
      tokenTypes: [],
      focused:    false,
      results:    {},
      style:      {},
    };
  }
  componentWillMount = () => {
    if (window.DP_HAS_NEW_SEARCH) {
      this.initTokenTypes();
    }
    window.document.addEventListener('dpLeftDrawerOpened', this.focus);
  };

  componentWillUnmount = () => {
    window.document.removeEventListener('dpLeftDrawerOpened', this.focus);
  };

  initTokenTypes = () => {
    const { intl } = this.props;
    const tokenTypes = [
      {
        id:     'status',
        label:  intl.formatMessage({ id: 'agent.general.status' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: [
              { label: intl.formatMessage({ id: 'agent.tickets.status_awaiting_agent' }), value: 'awaiting_agent' },
              { label: intl.formatMessage({ id: 'agent.tickets.status_awaiting_user' }), value: 'awaiting_user' },
              { label: intl.formatMessage({ id: 'agent.tickets.status_resolved' }), value: 'resolved' },
              { label: intl.formatMessage({ id: 'agent.tickets.status_archived' }), value: 'archived' },
            ],
          },
          renderHeader: <h3><FormattedMessage id="agent.general.status" /></h3>,
          showSearch:   false,
          isMultiple:   true,
        },
        allowDuplicate: false,
        description:    'Status of the ticket'
      },
      {
        id:     'department',
        label:  intl.formatMessage({ id: 'agent.general.department' }).toLowerCase().replace(/ /, '-'),
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
          isMultiple: true,
        },
        allowDuplicate: false,
        showOnFocus:    true,
      },
      {
        id:     'agent',
        label:  intl.formatMessage({ id: 'agent.general.agent' }).toLowerCase().replace(/ /, '-'),
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
          isMultiple: true,
        },
        allowDuplicate: false,
        showOnFocus:    true,
      },
      {
        id:     'agent_team',
        label:  intl.formatMessage({ id: 'agent.general.agent_team' }).toLowerCase().replace(/ /, '-'),
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
        allowDuplicate: false,
      },
      {
        id:     'ticket_product',
        label:  intl.formatMessage({ id: 'agent.general.product' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadProducts({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_category',
        label:  intl.formatMessage({ id: 'agent.general.category' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadCategories({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_priority',
        label:  intl.formatMessage({ id: 'agent.general.priority' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPriorities({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'urgency',
        label:  intl.formatMessage({ id: 'agent.general.urgency' }).toLowerCase().replace(/ /, '-'),
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
          isMultiple: true,
        },
        allowDuplicate: false,
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_workflow',
        label:  intl.formatMessage({ id: 'agent.general.workflow' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadWorkflows({ search }))
          },
          isMultiple: true,
        },
        allowDuplicate: false,
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:             'subject',
        label:          intl.formatMessage({ id: 'agent.general.subject' }).toLowerCase().replace(/ /, '-'),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'date_created',
        label:          intl.formatMessage({ id: 'agent.general.date_created' }).toLowerCase().replace(/ /, '-'),
        widget:         'DateTimeInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'date_resolved',
        label:          intl.formatMessage({ id: 'agent.general.date_resolved' }).toLowerCase().replace(/ /, '-'),
        widget:         'DateTimeInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'last_agent_reply',
        label:          'last-agent-reply',
        widget:         'DateTimeInput',
        props:          {},
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:             'last_user_reply',
        label:          'last-user-reply',
        widget:         'DateTimeInput',
        props:          {},
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:             'user_waiting',
        label:          'user-waiting',
        widget:         'DurationInput',
        props:          {},
        description:    'Time waited by user',
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:     'person_name',
        label:  intl.formatMessage({ id: 'agent.general.name' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPersons({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
      },
      {
        id:             'email_address',
        label:          intl.formatMessage({ id: 'agent.general.email_address' }).toLowerCase().replace(/ /, '-'),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:     'followers',
        label:  intl.formatMessage({ id: 'agent.general.followers' }).toLowerCase().replace(/ /, '-'),
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
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:     'organization',
        label:  intl.formatMessage({ id: 'agent.general.organization' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadOrganizations({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
      },
      {
        id:     'sla',
        label:  intl.formatMessage({ id: 'agent.general.sla' }).toLowerCase().replace(/ /, '-'),
        widget: 'BooleanInput',
        props:  {
          translations: {
            true:  intl.formatMessage({ id: 'agent.general.yes' }),
            false: intl.formatMessage({ id: 'agent.general.no' }),
          }
        },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:     'sla_status',
        label:  intl.formatMessage({ id: 'agent.general.sla_status' }).toLowerCase().replace(/ /, '-'),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadSlas({ search }))
          },
          isMultiple: true,
        },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
      },
      {
        id:     'brand',
        label:  intl.formatMessage({ id: 'agent.general.brand' }).toLowerCase().replace(/ /, '-'),
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
          isMultiple: true,
        },
        allowDuplicate: false,
      },
      {
        id:             'id',
        label:          intl.formatMessage({ id: 'agent.general.id' }).toLowerCase().replace(/ /, '-'),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'file_name',
        label:          intl.formatMessage({ id: 'agent.general.file' }).toLowerCase().replace(/ /, '-'),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'file_size',
        label:          intl.formatMessage({ id: 'agent.general.filesize' }).toLowerCase().replace(/ /, '-'),
        widget:         'NumericRangeInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'filetype',
        label:          intl.formatMessage({ id: 'agent.general.filetype' }).toLowerCase().replace(/ /, '-'),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
    ];
    this.setState(tokenTypes);
    this.props.dispatch(searchActions.loadCustomFields()).then((res) => {
      const customFields = res.map(field => ({
        id:             `custom_field_${field.id}`,
        label:          field.title.toLowerCase().replace(/ /, '-'),
        widget:         this.tokenFromField(field),
        props:          this.tokenPropsFromField(field),
        description:    field.description,
        allowDuplicate: false,
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
      props.isMultiple = true;
    }
    return props;
  };

  handleChange = (value) => {
    let results = {};
    if (value.length > 2) {
      results = fakeResults;
    }
    this.setState({
      results
    });
    this.updateStyle(results);
  };

  updateStyle = (results) => {
    let style = {};
    if (Object.keys(results).length === 0) {
      style = {
        height: 48
      };
    }
    if (JSON.stringify(this.state.style) !== JSON.stringify(style)) {
      this.setState({
        style
      });
      this.props.updateStyle(style);
    }
  };

  closeMenu = () => {
    if (this.tokenField) {
      this.tokenField.blur();
    }
    this.setState({
      value:   [],
      results: {}
    });
    this.props.closeMenu();
  };

  focus = () => {
    if (this.tokenField) {
      this.tokenField.focus();
    }
  };

  renderResults() {
    const { results } = this.state;
    if (Object.keys(results).length === 0) {
      return null;
    }
    return (
      <SearchResults
        results={results}
      />
    );
  }

  render() {
    const { tokenTypes } = this.state;
    return (
      <div id="search_menu">
        <div className="top">
          <Isvg
            className="search"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/search.svg`}
          />
          <TokenField
            ref={(c) => { this.tokenField = c; }}
            tokenTypes={tokenTypes}
            onChange={this.handleChange}
            placeholder=""
            zIndex={1800}
            showTokensOnFocus
          />
          <a className="close-icon" onClick={this.closeMenu}>
            <Isvg
              className="close-icon"
              src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
            />
          </a>
        </div>
        {this.renderResults()}
      </div>
    );
  }
}

export default injectIntl(SearchContainer, { withRef: true });
