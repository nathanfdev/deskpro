import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl } from 'react-intl';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import TokenField from '@deskpro/token-field/dist/index';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import * as searchActions from '../Actions/searchActions';
import SearchResults from './SearchResults';

@connect(state => ({
  chatDepartments:          collectionSelectorFactory('Department', 'all_chat')(state),
  ticketDepartments:        collectionSelectorFactory('Department', 'all_tickets')(state),
  agents:                   agentsSelector(state),
  agentTeams:               allSelectorFactory('AgentTeam')(state),
  brands:                   allSelectorFactory('Brand')(state),
  languages:                allSelectorFactory('Language')(state),
  personCustomFields:       allSelectorFactory('PersonCustomFields')(state),
  ticketCustomFields:       allSelectorFactory('TicketCustomFields')(state),
  organizationCustomFields: allSelectorFactory('OrganizationCustomFields')(state),
  slas:                     allSelectorFactory('Slas')(state),
}))
class SearchContainer extends React.Component {
  static propTypes = {
    intl:                     PropTypes.object,
    chatDepartments:          PropTypes.object,
    ticketDepartments:        PropTypes.object,
    agents:                   PropTypes.object,
    agentTeams:               PropTypes.object,
    brands:                   PropTypes.object,
    languages:                PropTypes.object,
    personCustomFields:       PropTypes.object,
    ticketCustomFields:       PropTypes.object,
    organizationCustomFields: PropTypes.object,
    slas:                     PropTypes.object,
    dispatch:                 PropTypes.func.isRequired,
    closeMenu:                PropTypes.func.isRequired,
    updateStyle:              PropTypes.func,
  };

  static defaultProps = {
    updateStyle() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      value:         [],
      tokenTypes:    [],
      menuStructure: [],
      focused:       false,
      results:       {},
      style:         {
        height: 48
      },
      scopes: [],
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
        id:     'ticket_status',
        label:  intl.formatMessage({ id: 'agent.general.status' }),
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
          showSearch: false,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        description:    'Status of the ticket',
        showOnFocus:    true,
      },
      {
        id:     'ticket_department',
        label:  intl.formatMessage({ id: 'agent.general.department' }),
        widget: 'DepartmentInput',
        props:  {
          dataSource: {
            getOptions: this.props.ticketDepartments
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        showOnFocus:    true,
      },
      {
        id:     'chat_department',
        label:  intl.formatMessage({ id: 'agent.general.department' }),
        widget: 'DepartmentInput',
        props:  {
          dataSource: {
            getOptions: this.props.chatDepartments
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Chat'],
      },
      {
        id:     'agent',
        label:  intl.formatMessage({ id: 'agent.general.agent' }),
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
        scopes:         ['Ticket', 'Task', 'Chat'],
        showOnFocus:    true,
        category:       intl.formatMessage({ id: 'agent.general.chat' }).toLowerCase(),
      },
      {
        id:     'agent_team',
        label:  intl.formatMessage({ id: 'agent.general.agent_team' }),
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
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'ticket_product',
        label:  intl.formatMessage({ id: 'agent.general.product' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadProducts({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_category',
        label:  intl.formatMessage({ id: 'agent.general.category' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadCategories({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'organization_label',
        label:  intl.formatMessage({ id: 'agent.general.label' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadOrganizationLabels({ search })).then(options => options.map(label => ({
              label: (
                <span className="label" style={{ color: label.text_color, backgroundColor: label.color }}>
                  {label.label}
                </span>
                     ),
              content: label.label,
              value:   label.label,
            }
              )))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Organization'],
        category:       intl.formatMessage({ id: 'agent.general.organization' }).toLowerCase(),
      },
      {
        id:     'person_label',
        label:  intl.formatMessage({ id: 'agent.general.label' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPersonLabels({ search })).then(options => options.map(label => ({
              label: (
                <span className="label" style={{ color: label.text_color, backgroundColor: label.color }}>
                  {label.label}
                </span>
                     ),
              content: label.label,
              value:   label.label,
            }
              )))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Person'],
        category:       intl.formatMessage({ id: 'agent.general.person' }).toLowerCase(),
      },
      {
        id:     'ticket_label',
        label:  intl.formatMessage({ id: 'agent.general.label' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadTicketLabels({ search })).then(options => options.map(label => ({
              label: (
                <span className="label" style={{ color: label.text_color, backgroundColor: label.color }}>
                  {label.label}
                </span>
                     ),
              content: label.label,
              value:   label.label,
            }
              )))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_priority',
        label:  intl.formatMessage({ id: 'agent.general.priority' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPriorities({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'urgency',
        label:  intl.formatMessage({ id: 'agent.general.urgency' }),
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
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:     'ticket_workflow',
        label:  intl.formatMessage({ id: 'agent.general.workflow' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadWorkflows({ search }))
          },
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      },
      {
        id:             'ticket-subject',
        label:          intl.formatMessage({ id: 'agent.general.subject' }),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:             'date_created',
        label:          intl.formatMessage({ id: 'agent.general.date_created' }),
        widget:         'DateTimeInput',
        props:          { locale: window.DP_LOCALE },
        allowDuplicate: false,
      },
      {
        id:             'date_resolved',
        label:          intl.formatMessage({ id: 'agent.general.date_resolved' }),
        widget:         'DateTimeInput',
        props:          { locale: window.DP_LOCALE },
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:             'last_agent_reply',
        label:          'last-agent-reply',
        widget:         'DateTimeInput',
        props:          { locale: window.DP_LOCALE },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:             'last_user_reply',
        label:          'last-user-reply',
        widget:         'DateTimeInput',
        props:          { locale: window.DP_LOCALE },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:             'user_waiting',
        label:          'user-waiting',
        widget:         'DurationInput',
        props:          {},
        description:    'Time waited by user',
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'person_name',
        label:  intl.formatMessage({ id: 'agent.general.name' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadPersons({ search }))
          },
          showSearch: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket', 'Chat', 'Person'],
      },
      {
        id:             'email_address',
        label:          intl.formatMessage({ id: 'agent.general.email_address' }),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
        scopes:         ['Agent', 'Ticket', 'Chat', 'Person'],
      },
      {
        id:     'email_account',
        label:  intl.formatMessage({ id: 'agent.general.email_account' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadEmailAccounts({ search })).then(options => options.map(account => ({
              label: account.address,
              value: account.id,
            }
              )))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'language',
        label:  intl.formatMessage({ id: 'agent.general.language' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: this.props.languages.toArray()
                          .sort((a, b) => a.get('title') > b.get('title'))
                          .map(e => ({
                            label: (
                              <span>
                                <img src={e.get('flag_image')} alt={e.get('title')} />&nbsp;{e.get('title')}
                              </span>
                                   ),
                            content: e.get('title'),
                            value:   e.get('id'),
                          }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'followers',
        label:  intl.formatMessage({ id: 'agent.general.followers' }),
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
        scopes:         ['Ticket'],
      },
      {
        id:     'organization_name',
        label:  intl.formatMessage({ id: 'agent.general.organization' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadOrganizations({ search }))
          },
          showSearch: true,
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Ticket', 'Organization', 'Person'],
      },
      {
        id:     'sla',
        label:  intl.formatMessage({ id: 'agent.general.sla' }),
        widget: 'BooleanInput',
        props:  {
          translations: {
            true:  intl.formatMessage({ id: 'agent.general.yes' }),
            false: intl.formatMessage({ id: 'agent.general.no' }),
          }
        },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'sla_status',
        label:  intl.formatMessage({ id: 'agent.general.sla_status' }),
        widget: 'SlaStatusInput',
        props:  {
          dataSource: {
            getOptions: this.props.slas.toList()
          },
          isMultiple:   true,
          translations: {
            any:        intl.formatMessage({ id: 'agent.general.any_sla' }),
            is:         intl.formatMessage({ id: 'agent.general.is' }),
            isNot:      intl.formatMessage({ id: 'agent.general.is_not' }),
            ok:         intl.formatMessage({ id: 'agent.general.ok' }),
            on:         intl.formatMessage({ id: 'agent.general.on' }).toLowerCase(),
            warning:    intl.formatMessage({ id: 'agent.general.warning' }),
            fail:       intl.formatMessage({ id: 'admin.general.fail' }),
            sla_status: intl.formatMessage({ id: 'agent.general.sla_status' }),
          }
        },
        category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
        allowDuplicate: false,
        scopes:         ['Ticket'],
      },
      {
        id:     'usergroups',
        label:  intl.formatMessage({ id: 'agent.general.usergroup' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: search => this.props.dispatch(searchActions.loadUserGroups({ search }))
          },
          isMultiple: true,
        },
        allowDuplicate: false,
        scopes:         ['Person', 'Organization'],
      },
      {
        id:     'brand',
        label:  intl.formatMessage({ id: 'agent.general.brand' }),
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
        scopes:         ['Ticket', 'Content', 'Organization'],
      },
      {
        id:             'id',
        label:          intl.formatMessage({ id: 'agent.general.id' }),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
      },
      {
        id:             'email_domain',
        label:          intl.formatMessage({ id: 'agent.general.email_domain' }),
        widget:         'TextInput',
        props:          {},
        allowDuplicate: false,
        scopes:         ['Person', 'Organization'],
      },
      {
        id:     'content_type',
        label:  intl.formatMessage({ id: 'agent.general.content_type' }),
        widget: 'SelectInput',
        props:  {
          dataSource: {
            getOptions: [
              {
                value: 'article',
                label: intl.formatMessage({ id: 'agent.general.article' })
              },
              {
                value: 'news',
                label: intl.formatMessage({ id: 'agent.general.news' })
              },
              {
                value: 'download',
                label: intl.formatMessage({ id: 'agent.general.download' })
              },
              {
                value: 'feedback',
                label: intl.formatMessage({ id: 'agent.general.feedback' })
              },
              {
                value: 'guide',
                label: intl.formatMessage({ id: 'agent.general.guide' })
              },
            ]
          },
          isMultiple: true,
        },
        scopes: ['Content'],
      }
    ];
    const ticketCustomFields = this.props.ticketCustomFields.toArray().map(field => ({
      id:             `ticket_custom_field_${field.get('id')}`,
      label:          field.get('title'),
      widget:         this.tokenFromField(field),
      props:          this.tokenPropsFromField(field),
      description:    field.get('description'),
      allowDuplicate: false,
      category:       intl.formatMessage({ id: 'agent.general.ticket' }).toLowerCase(),
      scopes:         ['Ticket'],
    }));
    const personCustomFields = this.props.personCustomFields.toArray().map(field => ({
      id:             `person_custom_field_${field.get('id')}`,
      label:          field.get('title'),
      widget:         this.tokenFromField(field),
      props:          this.tokenPropsFromField(field),
      description:    field.get('description'),
      allowDuplicate: false,
      category:       intl.formatMessage({ id: 'agent.general.person' }).toLowerCase(),
      scopes:         ['Person'],
    }));
    const organizationCustomFields = this.props.organizationCustomFields.toArray().map(field => ({
      id:             `organization_custom_field_${field.get('id')}`,
      label:          field.get('title'),
      widget:         this.tokenFromField(field),
      props:          this.tokenPropsFromField(field),
      description:    field.get('description'),
      allowDuplicate: false,
      category:       intl.formatMessage({ id: 'agent.general.organization' }).toLowerCase(),
      scopes:         ['Organization'],
    }));
    const ticketMenu = [
      {
        label: intl.formatMessage({ id: 'agent.general.status' }),
        token: 'ticket_status'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.agent' }),
        token: 'agent'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.brand' }),
        token: 'brand'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.department' }),
        token: 'ticket_department'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.product' }),
        token: 'ticket_product'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.subject' }),
        token: 'ticket-subject'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.person' }),
        token: 'person_name'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.organization' }),
        token: 'organization_name'
      },
    ];

    ticketCustomFields.forEach((customField) => {
      ticketMenu.push({
        label: customField.label,
        token: customField.id
      });
    });

    const personMenu = [
      {
        label: intl.formatMessage({ id: 'agent.general.name' }),
        token: 'person_name'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.email_address' }),
        token: 'email_address'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.email_domain' }),
        token: 'email_domain'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.organization' }),
        token: 'organization_name'
      },
    ];

    personCustomFields.forEach((customField) => {
      personMenu.push({
        label: customField.label,
        token: customField.id
      });
    });

    const organizationMenu = [
      {
        label: intl.formatMessage({ id: 'agent.general.name' }),
        token: 'organization_name'
      },
      {
        label: intl.formatMessage({ id: 'agent.general.email_domain' }),
        token: 'email_domain'
      },
    ];

    organizationCustomFields.forEach((customField) => {
      organizationMenu.push({
        label: customField.label,
        token: customField.id
      });
    });

    const menuStructure = [
      {
        label: intl.formatMessage({ id: 'agent.general.id' }),
        token: 'id',
      },
      {
        label:    intl.formatMessage({ id: 'agent.general.ticket' }),
        scope:    'Ticket',
        children: ticketMenu
      },
      {
        label:    intl.formatMessage({ id: 'agent.general.person' }),
        scope:    'Person',
        children: personMenu
      },
      {
        label:    intl.formatMessage({ id: 'agent.general.organization' }),
        scope:    'Organization',
        children: organizationMenu
      },
      {
        label:    intl.formatMessage({ id: 'agent.general.content' }),
        scope:    'Content',
        children: [
          {
            label: intl.formatMessage({ id: 'agent.general.brand' }),
            token: 'brand'
          },
          {
            label: intl.formatMessage({ id: 'agent.general.type' }),
            token: 'content_type'
          }
        ]
      }
    ];
    this.setState({
      tokenTypes: tokenTypes.concat(ticketCustomFields).concat(personCustomFields).concat(organizationCustomFields),
      menuStructure
    });
  };

  tokenFromField = (field) => {
    switch (field.get('widget_type')) {
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
    if (field.get('choices').size) {
      props.dataSource = {
        getOptions: field.get('choices')
                      .sort((a, b) => a.get('title') > b.get('title'))
                      .toArray()
                      .map(e => ({
                        label: e.get('title'),
                        value: e.get('id'),
                      }))
      };
      props.isMultiple = true;
    }
    return props;
  };

  handleChange = (value) => {
    if (value.length !== 0) {
      this.props.dispatch(searchActions.search(value)).then((results) => {
        this.setState({
          results
        });
        this.updateStyle(results);
      });
    }
  };

  handleScopesChange = (scopes) => {
    this.setState({
      scopes
    });
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
    const { results, scopes } = this.state;
    if (Object.keys(results).length === 0) {
      return null;
    }
    return (
      <SearchResults
        scopes={scopes}
        results={results}
      />
    );
  }

  render() {
    const { tokenTypes, menuStructure } = this.state;
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
            menuStructure={menuStructure}
            onChange={this.handleChange}
            onScopesChange={this.handleScopesChange}
            placeholder=""
            zIndex={1800}
            nbCollapsed={3}
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
