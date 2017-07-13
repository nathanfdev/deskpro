import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import Modal from 'deskpro-components/lib/Components/Modal';
import Button from 'deskpro-components/lib/Components/Button';
import ConfirmButton from 'deskpro-components/lib/Components/ConfirmButton';
import { Checkbox, Input, Label, TagInput, Select } from 'deskpro-components/lib/Components/Forms';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { allSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import * as actions from '../Actions/snippetsActions';
import { allSnippetBlobsSelector } from '../Selectors/snippets';

class LanguageOption extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string,
    isFocused: PropTypes.bool,
    onFocus:   PropTypes.func,
    onSelect:  PropTypes.func,
    option:    PropTypes.object.isRequired,
  };

  handleMouseDown = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onSelect(this.props.option, event);
  };

  handleMouseEnter = (event) => {
    this.props.onFocus(this.props.option, event);
  };

  handleMouseMove = (event) => {
    if (this.props.isFocused) return;
    this.props.onFocus(this.props.option, event);
  };

  render() {
    return (
      <div
        className={this.props.className}
        onMouseDown={this.handleMouseDown}
        onMouseEnter={this.handleMouseEnter}
        onMouseMove={this.handleMouseMove}
        title={this.props.option.title}
      >
        <img src={this.props.option.flag_image} role="presentation" />&nbsp;
        {this.props.children}
      </div>
    );
  }
}
class LanguageValue extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    value:    PropTypes.object
  };

  render() {
    if (!this.props.value) {
      return null;
    }
    return (
      <div className="Select-value" title={this.props.value.title}>
        <span className="Select-value-label">
          <img src={this.props.value.flag_image} role="presentation" />&nbsp;
          {this.props.children}
        </span>
      </div>
    );
  }
}
class VariableValue extends React.Component {
  render() {
    return (
      <div className="Select-value">
        <span className="Select-value-label">
          <i className="fa fa-dollar" />&nbsp;
          Variables
        </span>
      </div>
    );
  }
}

@connect(state => ({
  languages:            allSelectorFactory('Language')(state),
  chatDepartments:      collectionSelectorFactory('Department', 'all_chat')(state),
  userChatCustomFields: allSelectorFactory('UserChatCustomFields')(state),
  personCustomFields:   allSelectorFactory('PersonCustomFields')(state),
  ticketCustomFields:   allSelectorFactory('TicketCustomFields')(state),
  ticketDepartments:    collectionSelectorFactory('Department', 'all_tickets')(state),
  agentTeams:           allSelectorFactory('AgentTeam')(state),
}))
export class SnippetsModalContainer extends React.Component {
  static propTypes = {
    snippet:              PropTypes.object,
    languages:            PropTypes.object,
    userChatCustomFields: PropTypes.object,
    personCustomFields:   PropTypes.object,
    ticketCustomFields:   PropTypes.object,
    chatDepartments:      PropTypes.object,
    ticketDepartments:    PropTypes.object,
    agentTeams:           PropTypes.object,
    langId:               PropTypes.number,
    closeModal:           PropTypes.func,
    dispatch:             PropTypes.func,
    type:                 PropTypes.string,
  };
  static defaultProps = {
    type: 'ticket'
  };

  constructor(props) {
    super(props);
    this.state = {
      labels:       [],
      translations: [],
      departments:  [],
      teams:        [],
      types:        [],
      title:        this.props.snippet.get('title', ''),
      shortcutCode: this.props.snippet.get('shortcut_code', ''),
      isDraft:      false,
      langId:       props.langId,
    };
  }

  componentWillMount() {
    const { snippet, ticketDepartments, chatDepartments, agentTeams, type } = this.props;
    const departments = snippet.get('visible_departments', new Immutable.List()).toArray().map(id => `${id}`);
    const teams = snippet.get('ownership_teams', new Immutable.List()).toArray().map(id => `${id}`);
    const types = snippet.get('types', new Immutable.List([type])).toArray();
    if (snippet.get('is_visible_global')) {
      if (types.find(t => t === 'ticket')) {
        ticketDepartments.forEach((department) => {
          departments.push(`${department.get('id')}`);
        });
      }
      if (types.find(t => t === 'chat')) {
        chatDepartments.forEach((department) => {
          departments.push(`${department.get('id')}`);
        });
      }
    }
    if (snippet.get('is_ownership_global')) {
      agentTeams.forEach((team) => {
        teams.push(`${team.get('id')}`);
      });
    }
    this.setState({
      translations: snippet.get('translations', []),
      labels:       snippet.get('labels', new Immutable.List()).toArray(),
      types,
      isDraft:      snippet.get('is_draft', false),
      departments:  Array.from(new Set(departments)),
      teams,
    });
  }

  componentDidMount() {
    if (typeof DeskPRO_Window !== 'undefined') { // eslint-disable-line camelcase
      const self = this;
      const buttons = [
        'bold', 'italic', '|',
        'formatting', 'fontcolor', '|',
        'alignment', 'unorderedlist', 'outdent', 'indent', '|',
        'table', 'image', 'link', 'horizontalrule', '|',
        'html'
      ];
      DeskPRO_Window.initRteAgentReply(this.modal.textArea, { // eslint-disable-line no-undef
        defaultIsHtml: true,
        autoresize:    false,
        focus:         true,
        cleanup:       true,
        buttons,
        interval:      1,
        callback(obj) {
          self.redactor = obj;
        }
      });
    }
  }

  setLanguage = (value) => {
    this.saveTranslation();
    this.setState({
      langId: value.id
    });
    const translation = this.state.translations.find(t => t.get('language') === value.id);
    if (translation) {
      this.redactor.setCode(translation.get('content'));
    } else {
      this.redactor.setCode('');
    }
  };

  saveTranslation = () => {
    const index = this.state.translations.findIndex(translation => translation.get('language') === this.state.langId);
    let translations = {};
    if (index !== -1) {
      translations = this.state.translations.update(index, translation =>
        translation.set('content', this.redactor.getCode().replace(/^<p>/, '').replace(/(<p>)?<\/p>\s*$/, '')));
    } else {
      translations = this.state.translations.push(Immutable.fromJS({
        language: this.state.langId,
        content:  this.redactor.getCode(),
        blobs:    [],
      }));
    }
    this.setState({
      translations
    });
    return translations;
  };

  saveSnippet = () => {
    const { snippet, dispatch, closeModal, type } = this.props;
    const translations = this.saveTranslation();
    let isVisibleGlobal   = false;
    let isOwnershipGlobal = false;
    if (this.state.teams.length === this.props.agentTeams.size) {
      isOwnershipGlobal = true;
    }
    if (type === 'ticket') {
      if (this.state.departments.length === this.props.ticketDepartments.size) {
        isVisibleGlobal = true;
      }
    } else if (this.state.departments.length === this.props.chatDepartments.size) {
      isVisibleGlobal = true;
    }
    const snippetData = {
      title:               this.state.title,
      types:               this.state.types,
      shortcut_code:       this.state.shortcutCode,
      labels:              this.state.labels,
      translations:        translations.toJS(),
      is_draft:            this.state.isDraft,
      is_visible_global:   isVisibleGlobal,
      is_ownership_global: isOwnershipGlobal,
      ownership_teams:     isOwnershipGlobal ? [] : this.state.teams,
      visible_departments: isVisibleGlobal ? [] : this.state.departments,
    };
    if (snippet.get('id', false)) {
      snippetData.id = snippet.get('id');
    }
    dispatch(actions.saveSnippet(snippetData))
      .then((newSnippet) => {
        const shortcodes = window.DESKPRO_TICKET_SNIPPET_SHORTCODES;
        if (shortcodes[newSnippet.shortcut_code]) {
          shortcodes[newSnippet.shortcut_code] = shortcodes[newSnippet.shortcut_code].concat([newSnippet.id]);
        } else {
          shortcodes[newSnippet.shortcut_code] = [newSnippet.id];
        }
        closeModal();
      }, (error) => {
        console.log(error);
      })
    ;
  };

  deleteSnippet = () => {
    const { snippet, dispatch, closeModal } = this.props;
    dispatch(actions.deleteSnippet(snippet.get('id')))
      .then(() => {
        closeModal();
      }, (error) => {
        console.log(error);
      })
    ;
  };

  addAttachment = (event, data) => {
    const blob = data.result && data.result.data ? data.result.data : {};
    // Push new blob to collection
    this.props.dispatch(actions.addSnippetAttachment(blob));

    // Add blob to translation Map
    const index = this.state.translations.findIndex(translation => translation.get('language') === this.state.langId);

    let translations = {};
    if (index !== -1) {
      translations = this.state.translations.update(index, translation =>
        translation.update('blobs', blobs => blobs.push(blob.blob_id))
      );
    } else {
      translations = this.state.translations.push(Immutable.fromJS({
        language: this.state.langId,
        content:  this.redactor.getCode(),
        blobs:    [blob.blob_id],
      }));
    }
    this.setState({
      translations
    });
  };

  handleChangeDraft = (checked) => {
    this.setState({
      isDraft: checked
    });
  };

  handleChangeTypes = (checked, value) => {
    let types = this.state.types.slice(0);
    if (checked) {
      if (types.indexOf(value) === -1) {
        types.push(value);
      }
    } else {
      types = types.filter(type => type !== value);
    }
    if (types.length > 0) {
      this.setState({
        types
      });
    } else {
      alert('Snippet needs to have at least one type selected'); // eslint-disable-line no-alert
    }
  };

  handleDepartmentsChange = (departments) => {
    let next = departments;
    const previous = this.state.departments;
    const added = next.filter(i => previous.indexOf(i) < 0);
    const removed = previous.filter(i => next.indexOf(i) < 0);
    if (added.length === 1) {
      const addedId = parseInt(added[0], 10);
      let department = this.props.ticketDepartments.get(addedId);
      if (!department) {
        department = this.props.chatDepartments.get(addedId);
      }
      if (department && department.get('children')) {
        department.get('children').forEach((child) => {
          if (next.indexOf(`${child}`) === -1) {
            next.push(`${child}`);
          }
        });
      }
      if (department && department.get('parent')) {
        let parent = this.props.ticketDepartments.get(department.get('parent'));
        if (!parent) {
          parent = this.props.chatDepartments.get(department.get('parent'));
        }
        if (parent.get('children').filter(e => next.indexOf(`${e}`) < 0).size === 0) {
          next.push(`${parent.get('id')}`);
        }
      }
    }
    if (removed.length === 1) {
      const removedId = parseInt(removed[0], 10);
      let department = this.props.ticketDepartments.get(removedId);
      if (!department) {
        department = this.props.chatDepartments.get(removedId);
      }
      if (department && department.get('children')) {
        department.get('children').forEach((child) => {
          next = next.filter(item => parseInt(item, 10) !== child);
        });
      }
      if (department && department.get('parent')) {
        let parent = this.props.ticketDepartments.get(department.get('parent'));
        if (!parent) {
          parent = this.props.chatDepartments.get(department.get('parent'));
        }
        next = next.filter(item => parseInt(item, 10) !== parent.get('id'));
      }
    }
    this.setState({
      departments: next
    });
  };

  handleTeamsChange = (teams) => {
    this.setState({
      teams
    });
  };

  handleTitle = (title) => {
    this.setState({
      title
    });
  };

  handleShortcutCode = (shortcutCode) => {
    this.setState({
      shortcutCode
    });
  };

  changeLabels = (labels) => {
    this.setState({
      labels
    });
  };

  insertVariable = (variable) => {
    if (!variable) {
      return null;
    }
    try {
      this.redactor.restoreSelection();
      this.redactor.setBuffer();
    } catch (e) {
      console.log('Error retrieving redactor: %o', e);
    }
    this.redactor.insertHtml(`{{ ${variable.value} }}`);
    return true;
  };

  render() {
    const {
      snippet,
      closeModal,
      languages,
      userChatCustomFields,
      personCustomFields,
      ticketCustomFields,
      chatDepartments,
      ticketDepartments,
      agentTeams,
    } = this.props;

    let translation = this.state.translations.find(t => t.get('language') === this.state.langId);
    if (!translation) {
      translation = Immutable.fromJS({
        language: this.state.langId,
        content:  '',
        blobs:    [],
      });
    }
    return (
      <SnippetsModal
        snippet={snippet}
        labels={this.state.labels}
        translation={translation}
        closeModal={closeModal}
        languages={languages}
        userChatCustomFields={userChatCustomFields}
        personCustomFields={personCustomFields}
        ticketCustomFields={ticketCustomFields}
        chatDepartments={chatDepartments}
        ticketDepartments={ticketDepartments}
        snippetDepartments={this.state.departments}
        agentTeams={agentTeams}
        snippetTeams={this.state.teams}
        isDraft={this.state.isDraft}
        types={this.state.types}
        langId={this.state.langId}
        shortcutCode={this.state.shortcutCode}
        title={this.state.title}
        addAttachment={this.addAttachment}
        saveSnippet={this.saveSnippet}
        deleteSnippet={this.deleteSnippet}
        setLanguage={this.setLanguage}
        changeLabels={this.changeLabels}
        insertVariable={this.insertVariable}
        handleChangeDraft={this.handleChangeDraft}
        handleChangeTypes={this.handleChangeTypes}
        handleDepartmentsChange={this.handleDepartmentsChange}
        handleTeamsChange={this.handleTeamsChange}
        handleShortcutCode={this.handleShortcutCode}
        handleTitle={this.handleTitle}
        ref={(c) => { this.modal = c; }}
      />
    );
  }
}
export class SnippetsModal extends React.Component {
  static propTypes = {
    snippet:                 PropTypes.object,
    translation:             PropTypes.object,
    languages:               PropTypes.object,
    userChatCustomFields:    PropTypes.object,
    personCustomFields:      PropTypes.object,
    ticketCustomFields:      PropTypes.object,
    chatDepartments:         PropTypes.object,
    ticketDepartments:       PropTypes.object,
    snippetDepartments:      PropTypes.array,
    agentTeams:              PropTypes.object,
    snippetTeams:            PropTypes.array,
    isDraft:                 PropTypes.bool,
    types:                   PropTypes.array,
    langId:                  PropTypes.number,
    shortcutCode:            PropTypes.string,
    title:                   PropTypes.string,
    labels:                  PropTypes.array,
    addAttachment:           PropTypes.func,
    saveSnippet:             PropTypes.func,
    deleteSnippet:           PropTypes.func,
    setLanguage:             PropTypes.func,
    closeModal:              PropTypes.func,
    changeLabels:            PropTypes.func,
    insertVariable:          PropTypes.func,
    handleChangeDraft:       PropTypes.func,
    handleChangeTypes:       PropTypes.func,
    handleDepartmentsChange: PropTypes.func,
    handleShortcutCode:      PropTypes.func,
    handleTeamsChange:       PropTypes.func,
    handleTitle:             PropTypes.func,
  };
  static defaultProps = {
    changeLabels() {}
  };

  constructor(props) {
    super(props);
    this.height = window.innerHeight;
  }

  getVariables = () => {
    let variables = [];
    const { types, ticketCustomFields, personCustomFields, userChatCustomFields } = this.props;
    if (types.find(type => type === 'ticket')) {
      variables = [
        { value: 'ticket', label: agentPhrases.get('agent.general.ticket'), disabled: true },
        { value: 'entity.subject', label: agentPhrases.get('agent.general.subject') },
        { value: 'entity.department.title', label: agentPhrases.get('agent.general.department') },
        { value: 'entity.department.parent.title', label: agentPhrases.get('agent.general.department.parent') },
        { value: 'entity.brand.name', label: agentPhrases.get('agent.general.brand') },
        { value: 'entity.product.title', label: agentPhrases.get('agent.general.product') },
        { value: 'entity.category.title', label: agentPhrases.get('agent.general.category') },
        { value: 'entity.workflow.title', label: agentPhrases.get('agent.general.workflow') },
        { value: 'entity.priority.title', label: agentPhrases.get('agent.general.priority') },
        { value: 'entity.agent.display_name', label: agentPhrases.get('agent.general.agent') },
        { value: 'entity.agent.primary_email.email', label: agentPhrases.get('agent.general.agent_email_address') },
        { value: 'entity.agent_team.name', label: agentPhrases.get('agent.general.agent_team') }
      ];
      ticketCustomFields.forEach((field) => {
        variables.push({
          value: `ticket.field${field.get('id')}`,
          label: field.get('title')
        });
      });
    }
    if (types.find(type => type === 'chat')) {
      variables = variables.concat([
        { value: 'chat', label: agentPhrases.get('agent.general.chat'), disabled: true },
        { value: 'entity.subject', label: agentPhrases.get('agent.general.subject') },
        { value: 'entity.department.title', label: agentPhrases.get('agent.general.department') },
        { value: 'entity.department.parent.title', label: agentPhrases.get('agent.general.department.parent') },
        { value: 'entity.agent.display_name', label: agentPhrases.get('agent.general.agent') },
        { value: 'entity.agent.primary_email.email', label: agentPhrases.get('agent.general.agent_email_address') },
        { value: 'entity.agent_team.name', label: agentPhrases.get('agent.general.agent_team') }
      ]);
      userChatCustomFields.forEach((field) => {
        variables.push({
          value: `chat.field${field.get('id')}`,
          label: field.get('title')
        });
      });
    }
    variables = variables.concat([
      { value: 'user', label: agentPhrases.get('agent.general.user'), disabled: true },
      { value: 'entity.person.display_name', label: agentPhrases.get('agent.general.name') },
      { value: 'entity.person.primary_email.email', label: agentPhrases.get('agent.general.email_address') },
      { value: 'entity.person.organization.name', label: agentPhrases.get('agent.general.organization') },
      { value: 'entity.person.organization_position', label: agentPhrases.get('agent.general.org_position') },
    ]);

    personCustomFields.forEach((field) => {
      variables.push({
        value: `ticket.person.field${field.get('id')}`,
        label: field.get('title')
      });
    });
    return (
      <div className="variable-switch field">
        <Select
          options={variables}
          value={variables[0]}
          clearable={false}
          valueComponent={VariableValue}
          onChange={this.props.insertVariable}
        />
      </div>
    );
  };

  getDepartments = () => {
    const { types, ticketDepartments, chatDepartments } = this.props;
    const departments = [];
    const ids = [];
    if (types.find(type => type === 'ticket')) {
      ticketDepartments.forEach((department) => {
        if (ids.indexOf(department.get('id') === -1)) {
          let label = department.get('title');
          if (department.get('parent')) {
            label = `-- ${label}`;
          }
          departments.push({
            value:    `${department.get('id')}`,
            label,
            selected: !!this.props.snippetDepartments.find(d => parseInt(d, 10) === department.get('id')),
          });
          ids.push(department.get('id'));
        }
      });
    }
    if (types.find(type => type === 'chat')) {
      chatDepartments.forEach((department) => {
        if (ids.indexOf(department.get('id') === -1)) {
          let label = department.get('title');
          if (department.get('parent')) {
            label = `-- ${label}`;
          }
          departments.push({
            value:    `${department.get('id')}`,
            label,
            selected: !!this.props.snippetDepartments.find(d => parseInt(d, 10) === department.get('id')),
          });
          ids.push(department.get('id'));
        }
      });
    }
    return departments;
  };

  getTeams = () => {
    const { agentTeams } = this.props;
    const teams = [];
    agentTeams.forEach((team) => {
      teams.push({
        value:    `${team.get('id')}`,
        label:    team.get('name'),
        selected: this.props.snippetTeams.find(d => parseInt(d, 10) === team.get('id')),
      });
    });
    return teams;
  };

  getUploadUrl = () => '/api/v2/blobs/temp';

  isValid = () => this.isTitleValid() && this.isShortcutCodeValid();

  isTitleValid = () => this.props.title !== '';

  isShortcutCodeValid = () => this.props.shortcutCode.match(/^[-_a-z0-9]*$/i);

  render() {
    const {
      snippet,
      labels,
      translation,
      languages,
      langId,
      shortcutCode,
      title,
      setLanguage,
      handleChangeDraft,
      handleChangeTypes,
      handleShortcutCode,
      handleTitle,
      addAttachment,
      changeLabels,
      closeModal,
      saveSnippet,
      deleteSnippet,
    } = this.props;
    if (!snippet) {
      return null;
    }
    const languageOptions = languages.map((language) => {
      let option = language.set('label', language.get('title'));
      option = option.set('value', language.get('id'));
      return option.toJS();
    });
    return (
      <div id="snippets__modal">
        <Modal
          title={snippet.get('id', false) ? agentPhrases.get('agent.snippets.edit_snippet') : 'New snippet'}
          closeModal={closeModal}
          buttons={
            <div>
              <Button className="dp-button--l" onClick={saveSnippet} disabled={!this.isValid()}>
                {agentPhrases.get('agent.general.save')}
              </Button>
              <Button className="dp-button--l dp-button--secondary" onClick={closeModal}>
                {agentPhrases.get('agent.general.cancel')}
              </Button>
              <ConfirmButton
                className="dp-button--l dp-button--secondary right"
                onClick={deleteSnippet}
                disabled={!snippet.get('id')}
                message={agentPhrases.get('agent.general.are_you_sure')}
              >
                {agentPhrases.get('agent.general.delete')}
              </ConfirmButton>
            </div>
        }
        >
          <div className="language-switch field">
            <Select
              onChange={setLanguage}
              optionComponent={LanguageOption}
              options={languageOptions.toArray()}
              value={langId}
              clearable={false}
              valueComponent={LanguageValue}
            />
          </div>
          {this.getVariables()}
          <form id="snippet_form">
            <div className="title-field field">
              <Label htmlFor="snippet_title" required>{agentPhrases.get('agent.general.title')}</Label>
              <Input
                id="snippet_title"
                value={title}
                onChange={handleTitle}
                required
              />
            </div>
            <textarea
              id="snippet__editor"
              cols="30"
              rows="10"
              defaultValue={translation.get('content', '')}
              ref={(c) => { this.textArea = c; }}
            />
            <span className="files">
              {translation.get('blobs').map((blobId, key) => <SnippetAttachment key={key} blobId={blobId} />)}
            </span>
            <UploadButton
              id={'upload_attachment'}
              ref={(c) => { this.uploadButton = c; }}
              name="file"
              onSuccess={addAttachment}
              uploadUrl={this.getUploadUrl()}
            />
            <div className="labels-field field">
              <Label htmlFor="snippet_label_input">{agentPhrases.get('agent.general.labels')}</Label>
              <TagInput
                tags={labels}
                onChange={changeLabels}
                addOnBlur
                editable
                inputProps={{
                  placeholder: 'Add a label',
                  id:          'snippet_label_input'
                }}
              />
            </div>
            <div className="types-field field">
              <Label htmlFor="snippet_types_input">{agentPhrases.get('agent.general.types')}</Label>
              <Checkbox
                checked={this.props.types.find(type => type === 'ticket')}
                value="ticket"
                onChange={handleChangeTypes}
              >
                {agentPhrases.get('agent.general.ticket')}
              </Checkbox>
              <Checkbox
                checked={this.props.types.find(type => type === 'chat')}
                value="chat"
                onChange={handleChangeTypes}
              >
                {agentPhrases.get('agent.general.chat')}
              </Checkbox>
            </div>
            <div className="draft-field field">
              <Label htmlFor="snippet_draft_input">{agentPhrases.get('agent.general.draft')}</Label>
              <Checkbox
                checked={this.props.isDraft}
                value="is_draft"
                onChange={handleChangeDraft}
              >
                {agentPhrases.get('agent.snippets.snippet_is_draft')}
              </Checkbox>
            </div>
            <br />
            <div className="shortcut-field field">
              <Label htmlFor="snippet_shortcut_code">
                {agentPhrases.get('agent.snippets.shortcut_code')}
              </Label>
              <Input
                id="snippet_shortcut_code"
                className={classNames('snippet_shortcut_code', { 'dp-input--error': !this.isShortcutCodeValid() })}
                value={shortcutCode}
                prefix="%"
                suffix="%"
                required
                onChange={handleShortcutCode}
              />
            </div>
            <div className="ownership-field field">
              <Label htmlFor="snippet_ownership">{agentPhrases.get('agent.snippets.ownership')}</Label>
              <Select
                multiple
                includeSelectAllOption
                selectAllText={agentPhrases.get('agent.general.global')}
                allSelectedText={agentPhrases.get('agent.general.global')}
                nonSelectedText={agentPhrases.get('agent.general.myself')}
                maxHeight={this.height > 850 ? 300 : 150}
                nSelectedText={agentPhrases.get('agent.general.teams').toLowerCase()}
                value={this.props.snippetTeams}
                onChange={this.props.handleTeamsChange}
                options={this.getTeams()}
              />
            </div>
            <div className="visibility-field field">
              <Label htmlFor="snippet_visibility">{agentPhrases.get('agent.snippets.visibility')}</Label>
              <Select
                multiple
                includeSelectAllOption
                selectAllText={agentPhrases.get('agent.general.global')}
                allSelectedText={agentPhrases.get('agent.general.global')}
                nonSelectedText={agentPhrases.get('agent.general.none')}
                maxHeight={this.height > 850 ? 300 : 150}
                nSelectedText={agentPhrases.get('agent.general.departments').toLowerCase()}
                value={this.props.snippetDepartments}
                onChange={this.props.handleDepartmentsChange}
                options={this.getDepartments()}
              />
            </div>
          </form>
        </Modal>
      </div>
    );
  }
}
@connect(state => ({
  blobs: allSnippetBlobsSelector(state)
}))
class SnippetAttachment extends React.Component {
  static propTypes = {
    blobs:  PropTypes.object,
    blobId: PropTypes.number,
  };

  render() {
    const { blobId, blobs } = this.props;
    if (!blobs) {
      return null;
    }
    const blob = blobs.find(b => b.get('id') === blobId);
    return (
      <div className="snippet-attachment">
        <i className="fa fa-paperclip" />&nbsp;
        <strong>Attachment:</strong>&nbsp;
        {blob.get('filename')} ({blob.get('filesize_readable')})
        <Isvg
          className="close-icon"
          src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
        />
      </div>
    );
  }
}
