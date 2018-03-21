import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import htmlToText from 'html-to-text';
import { Button, ConfirmButton, Modal, Icon, Checkbox, Input, Label, TagInput, Select, Tabs, TabLink } from '@deskpro/react-components';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import * as actions from '../Actions/snippetsActions';
import { allSnippetBlobsSelector } from '../Selectors/snippets';
import { ModalLanguageSelect } from './Menus/ModalLanguageSelect';
import { OwnershipSelectContainer } from './Menus/OwnershipSelect';
import { VisibilitySelectContainer } from './Menus/VisibilitySelect';
import { UsageHistoryModal } from './UsageHistoryModal';
import { ChangeLogModal } from './ChangeLogModal';

class VariableValue extends React.Component {
  render() {
    return (
      <div className="Select-value">
        <span className="Select-value-label">
          <i className="fa fa-dollar" />&nbsp;
          <FormattedMessage id="agent.snippets.variables" />
        </span>
      </div>
    );
  }
}

@connect(state => ({
  me:                   meSelector(state),
  languages:            allSelectorFactory('Language')(state),
  chatDepartments:      collectionSelectorFactory('Department', 'all_chat')(state),
  userChatCustomFields: allSelectorFactory('UserChatCustomFields')(state),
  personCustomFields:   allSelectorFactory('PersonCustomFields')(state),
  ticketCustomFields:   allSelectorFactory('TicketCustomFields')(state),
  ticketDepartments:    collectionSelectorFactory('Department', 'all_tickets')(state),
}))
export class SnippetsModalContainer extends React.Component {
  static propTypes = {
    me:                   PropTypes.object,
    snippet:              PropTypes.object,
    languages:            PropTypes.object,
    userChatCustomFields: PropTypes.object,
    personCustomFields:   PropTypes.object,
    ticketCustomFields:   PropTypes.object,
    chatDepartments:      PropTypes.object,
    ticketDepartments:    PropTypes.object,
    labelsSource:         PropTypes.array,
    langId:               PropTypes.number,
    height:               PropTypes.number,
    closeModal:           PropTypes.func,
    dispatch:             PropTypes.func,
    type:                 PropTypes.string,
  };
  static defaultProps = {
    type: 'ticket'
  };

  constructor(props) {
    super(props);
    const { snippet, type } = this.props;
    const departments = snippet.get('visible_departments', new Immutable.List()).toArray();
    const teams = snippet.get('ownership_teams', new Immutable.List()).toArray();
    const types = snippet.get('types', new Immutable.List([type])).toArray();
    this.state = {
      labels:            snippet.get('labels', new Immutable.List()).toArray(),
      translations:      snippet.get('translations', []),
      departments:       new Set(departments),
      teams:             new Set(teams),
      saving:            false,
      type,
      types,
      isOwnershipGlobal: snippet.get('is_ownership_global'),
      isVisibleGlobal:   snippet.get('is_visible_global'),
      isSplit:           snippet.get('is_split', false),
      title:             snippet.get('title', ''),
      shortcutCode:      snippet.get('shortcut_code', ''),
      isDraft:           !!snippet.get('is_draft', false),
      langId:            props.langId,
      mergeKeepValue:    'ticket',
    };
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
      langId: value
    });
    const translation = this.state.translations.find(t =>
      t.get('language') === value && (!this.state.isSplit || t.get('type') === this.state.type)
    );
    if (translation) {
      this.redactor.setCode(translation.get('content'));
    } else {
      this.redactor.setCode('');
    }
  };

  setContent = (content) => {
    this.redactor.setCode(content);
    this.saveTranslation();
  };

  saveTranslation = () => {
    const index = this.state.translations.findIndex(t =>
      t.get('language') === this.state.langId && (!this.state.isSplit || t.get('type') === this.state.type)
    );
    let translations = {};
    if (index !== -1) {
      translations = this.state.translations.update(index, t =>
        t.set('content', this.redactor.getCode().replace(/^<p>/, '').replace(/(<p>)?<\/p>\s*$/, '')));
    } else {
      translations = this.state.translations.push(Immutable.fromJS({
        language: this.state.langId,
        content:  this.redactor.getCode(),
        type:     this.state.isSplit ? this.state.type : null,
        blobs:    [],
      }));
    }
    translations = translations.filter(t => htmlToText.fromString(t.get('content')).replace(/\s*/, '') !== '');
    this.setState({
      translations
    });
    return translations;
  };

  saveSnippet = () => {
    const { snippet, dispatch, closeModal } = this.props;
    if (this.state.saving) {
      return false;
    }
    this.setState({
      saving: true
    });
    const translations = this.saveTranslation();
    const snippetData = {
      title:               this.state.title,
      types:               this.state.types,
      shortcut_code:       this.state.shortcutCode,
      labels:              this.state.labels,
      translations:        translations.toJS(),
      is_split:            this.state.isSplit,
      is_draft:            this.state.isDraft,
      is_visible_global:   this.state.isVisibleGlobal,
      is_ownership_global: this.state.isOwnershipGlobal,
      ownership_teams:     [...this.state.teams],
      visible_departments: [...this.state.departments],
    };
    if (snippet.get('id', false)) {
      snippetData.id = snippet.get('id');
    }
    dispatch(actions.saveSnippet(snippetData))
      .then((newSnippet) => {
        const shortCodes = window.DESKPRO_TICKET_SNIPPET_SHORTCODES;
        const previousCode = snippet.get('shortcut_code');
        if (shortCodes[previousCode]
          && shortCodes[previousCode].indexOf(snippet.get('id') !== -1)) {
          shortCodes[previousCode] = shortCodes[previousCode].filter(i => i !== snippet.get('id'));
        }
        if (shortCodes[snippet.shortcut_code] && shortCodes[snippet.shortcut_code].indexOf(snippet.id) === -1) {
          shortCodes[newSnippet.shortcut_code] = shortCodes[newSnippet.shortcut_code].concat([newSnippet.id]);
        } else if (!shortCodes[snippet.shortcut_code]) {
          shortCodes[newSnippet.shortcut_code] = [newSnippet.id];
        }
        this.setState({
          saving: false
        });
        closeModal();
      }, (error) => {
        console.log(error);
      })
    ;
    return true;
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
    const index = this.state.translations.findIndex(t => t.get('language') === this.state.langId);

    let translations = {};
    if (index !== -1) {
      translations = this.state.translations.update(index, translation =>
        translation.update('blobs', blobs => blobs.push(blob.blob_id))
      );
    } else {
      translations = this.state.translations.push(Immutable.fromJS({
        language: this.state.langId,
        content:  this.redactor.getCode(),
        type:     this.state.isSplit ? this.state.type : null,
        blobs:    [blob.blob_id],
      }));
    }
    this.setState({
      translations
    });
  };

  removeAttachment = (blobId) => {
    // Add blob to translation Map
    const index = this.state.translations.findIndex(t => t.get('language') === this.state.langId);

    const translations = this.state.translations.update(index, translation =>
      translation.update('blobs', blobs => blobs.filter(blob => blob !== blobId))
    );
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

  handleDepartmentsChange = (departments, isVisibleGlobal) => {
    this.setState({
      departments,
      isVisibleGlobal,
    });
  };

  handleTeamsChange = (teams, isOwnershipGlobal) => {
    this.setState({
      teams,
      isOwnershipGlobal
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

  changeType = (type) => {
    this.saveTranslation();
    this.setState({
      type
    });
    const translation = this.state.translations.find(t =>
      t.get('language') === this.state.langId && t.get('type') === type
    );
    if (translation) {
      this.redactor.setCode(translation.get('content'));
    } else {
      this.redactor.setCode('');
    }
  };

  splitSnippet = (e) => {
    e.preventDefault();
    const { type, types } = this.state;
    let newTranslations = new Immutable.List();
    let translations = this.saveTranslation();
    translations = translations.map((t) => {
      let newType = t;
      newType = newType.set('type', type === 'ticket' ? 'chat' : 'ticket').delete('id').delete('snippet');
      newTranslations = newTranslations.push(newType);
      return t.set('type', type);
    });
    if (types.length < 2) {
      if (types[0] === 'ticket') {
        types.push('chat');
      } else {
        types.push('ticket');
      }
    }
    translations = translations.concat(newTranslations);
    this.setState({
      types,
      translations,
      isSplit: true,
    });
  };

  mergeSnippet = (e) => {
    e.preventDefault();
    const translations = this.saveTranslation().filter(t => t.get('type') === this.state.mergeKeepValue);
    this.setState({
      translations,
      isSplit: false,
    });
    const translation = translations.find(t =>
      t.get('language') === this.state.langId && t.get('type') === this.state.mergeKeepValue
    );
    if (translation) {
      this.redactor.setCode(translation.get('content'));
    } else {
      this.redactor.setCode('');
    }
    this.modal.displayMerge();
  };

  updateMergeKeep = (value) => {
    this.setState({
      mergeKeepValue: value.value
    });
  };

  render() {
    const {
      me,
      snippet,
      closeModal,
      languages,
      userChatCustomFields,
      personCustomFields,
      ticketCustomFields,
      chatDepartments,
      ticketDepartments,
      height,
    } = this.props;

    let translation = this.state.translations.find(t =>
      t.get('language') === this.state.langId && (!this.state.isSplit || t.get('type') === this.state.type)
    );
    if (!translation) {
      translation = Immutable.fromJS({
        language: this.state.langId,
        content:  '',
        type:     this.state.isSplit ? this.state.type : null,
        blobs:    [],
      });
    }
    return (
      <SnippetsModal
        me={me}
        snippet={snippet}
        labels={this.state.labels}
        labelsSource={this.props.labelsSource}
        translation={translation}
        translations={this.state.translations}
        closeModal={closeModal}
        languages={languages}
        userChatCustomFields={userChatCustomFields}
        personCustomFields={personCustomFields}
        ticketCustomFields={ticketCustomFields}
        chatDepartments={chatDepartments}
        ticketDepartments={ticketDepartments}
        snippetDepartments={this.state.departments}
        snippetTeams={this.state.teams}
        isDraft={this.state.isDraft}
        isOwnershipGlobal={this.state.isOwnershipGlobal}
        isVisibleGlobal={this.state.isVisibleGlobal}
        isSplit={this.state.isSplit}
        type={this.state.type}
        types={this.state.types}
        langId={this.state.langId}
        height={height}
        shortcutCode={this.state.shortcutCode}
        title={this.state.title}
        mergeKeepValue={this.state.mergeKeepValue}
        saving={this.state.saving}
        addAttachment={this.addAttachment}
        saveSnippet={this.saveSnippet}
        deleteSnippet={this.deleteSnippet}
        setLanguage={this.setLanguage}
        setContent={this.setContent}
        changeLabels={this.changeLabels}
        insertVariable={this.insertVariable}
        handleChangeDraft={this.handleChangeDraft}
        handleChangeTypes={this.handleChangeTypes}
        handleDepartmentsChange={this.handleDepartmentsChange}
        handleTeamsChange={this.handleTeamsChange}
        handleShortcutCode={this.handleShortcutCode}
        handleTitle={this.handleTitle}
        splitSnippet={this.splitSnippet}
        mergeSnippet={this.mergeSnippet}
        updateMergeKeep={this.updateMergeKeep}
        changeType={this.changeType}
        removeAttachment={this.removeAttachment}
        ref={(c) => { this.modal = c; }}
      />
    );
  }
}
export class SnippetsModal extends React.Component {
  static propTypes = {
    me:                      PropTypes.object,
    snippet:                 PropTypes.object,
    translation:             PropTypes.object,
    translations:            PropTypes.object,
    languages:               PropTypes.object,
    userChatCustomFields:    PropTypes.object,
    personCustomFields:      PropTypes.object,
    ticketCustomFields:      PropTypes.object,
    chatDepartments:         PropTypes.object,
    ticketDepartments:       PropTypes.object,
    snippetDepartments:      PropTypes.object,
    snippetTeams:            PropTypes.object,
    isDraft:                 PropTypes.bool.isRequired,
    isOwnershipGlobal:       PropTypes.bool.isRequired,
    isVisibleGlobal:         PropTypes.bool.isRequired,
    isSplit:                 PropTypes.bool.isRequired,
    saving:                  PropTypes.bool,
    type:                    PropTypes.string,
    types:                   PropTypes.array.isRequired,
    langId:                  PropTypes.number,
    height:                  PropTypes.number,
    shortcutCode:            PropTypes.string,
    title:                   PropTypes.string,
    mergeKeepValue:          PropTypes.string,
    labels:                  PropTypes.array,
    labelsSource:            PropTypes.array,
    addAttachment:           PropTypes.func,
    saveSnippet:             PropTypes.func,
    deleteSnippet:           PropTypes.func,
    setLanguage:             PropTypes.func,
    setContent:              PropTypes.func,
    closeModal:              PropTypes.func,
    changeLabels:            PropTypes.func,
    insertVariable:          PropTypes.func,
    handleChangeDraft:       PropTypes.func,
    handleChangeTypes:       PropTypes.func,
    handleDepartmentsChange: PropTypes.func,
    handleShortcutCode:      PropTypes.func,
    handleTeamsChange:       PropTypes.func,
    handleTitle:             PropTypes.func,
    splitSnippet:            PropTypes.func,
    mergeSnippet:            PropTypes.func,
    updateMergeKeep:         PropTypes.func,
    changeType:              PropTypes.func,
    removeAttachment:        PropTypes.func,
  };
  static defaultProps = {
    shortcutCode: '',
    changeLabels() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      displayMerge:          false,
      changeLogModalOpen:    false,
      usageHistoryModalOpen: false,
    };
  }

  onSubmit = (e) => {
    e.preventDefault();
  };

  getVariables = () => {
    let variables = [];
    const { types, ticketCustomFields, personCustomFields, userChatCustomFields } = this.props;
    if (types.find(type => type === 'ticket')) {
      variables = [
        { value: 'ticket', label: <FormattedMessage id="agent.general.ticket" />, disabled: true },
        { value: 'entity.subject', label: <FormattedMessage id="agent.general.subject" /> },
        { value: 'entity.department.title', label: <FormattedMessage id="agent.general.department" /> },
        { value: 'entity.department.parent.title', label: <FormattedMessage id="agent.general.department.parent" /> },
        { value: 'entity.brand.name', label: <FormattedMessage id="agent.general.brand" /> },
        { value: 'entity.product.title', label: <FormattedMessage id="agent.general.product" /> },
        { value: 'entity.category.title', label: <FormattedMessage id="agent.general.category" /> },
        { value: 'entity.workflow.title', label: <FormattedMessage id="agent.general.workflow" /> },
        { value: 'entity.priority.title', label: <FormattedMessage id="agent.general.priority" /> },
        { value: 'entity.agent.display_name', label: <FormattedMessage id="agent.general.agent" /> },
        { value: 'entity.agent.primary_email.email', label: <FormattedMessage id="agent.general.agent_email_address" /> },
        { value: 'entity.agent_team.name', label: <FormattedMessage id="agent.general.agent_team" /> }
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
        { value: 'chat', label: <FormattedMessage id="agent.general.chat" />, disabled: true },
        { value: 'entity.subject', label: <FormattedMessage id="agent.general.subject" /> },
        { value: 'entity.department.title', label: <FormattedMessage id="agent.general.department" /> },
        { value: 'entity.department.parent.title', label: <FormattedMessage id="agent.general.department.parent" /> },
        { value: 'entity.agent.display_name', label: <FormattedMessage id="agent.general.agent" /> },
        { value: 'entity.agent.primary_email.email', label: <FormattedMessage id="agent.general.agent_email_address" /> },
        { value: 'entity.agent_team.name', label: <FormattedMessage id="agent.general.agent_team" /> }
      ]);
      userChatCustomFields.forEach((field) => {
        variables.push({
          value: `chat.field${field.get('id')}`,
          label: field.get('title')
        });
      });
    }
    variables = variables.concat([
      { value: 'user', label: <FormattedMessage id="agent.general.user" />, disabled: true },
      { value: 'entity.person.display_name', label: <FormattedMessage id="agent.general.name" /> },
      { value: 'entity.person.first_name', label: <FormattedMessage id="agent.general.first_name" /> },
      { value: 'entity.person.last_name', label: <FormattedMessage id="agent.general.last_name" /> },
      { value: 'entity.person.primary_email.email', label: <FormattedMessage id="agent.general.email_address" /> },
      { value: 'entity.person.organization.name', label: <FormattedMessage id="agent.general.organization" /> },
      { value: 'entity.person.organization_position', label: <FormattedMessage id="agent.general.org_position" /> },
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

  getModalTitle = () => {
    const { snippet } = this.props;
    let usage = null;
    if (snippet.get('usage_count')) {
      usage = (<a className="usage_history" onClick={this.openUsageHistoryModal}>
        <FormattedMessage id="agent.snippets.usage_history" /> ({snippet.get('usage_count')})
      </a>);
    }
    return (
      <div>
        {snippet.get('id', false) ? <FormattedMessage id="agent.snippets.edit_snippet" /> : 'New snippet'}
        {usage}
        {snippet.get('id', false) ?
          <a className="change_log" onClick={this.openChangeLogModal}><FormattedMessage id="agent.general.changelog" /></a>
          : null }
      </div>
    );
  };

  getUsageHistoryModal = () => {
    if (!this.state.usageHistoryModalOpen) {
      return null;
    }
    return (
      <UsageHistoryModal
        snippet={this.props.snippet}
        closeModal={this.closeUsageHistoryModal}
      />
    );
  };

  getChangeLogModal = () => {
    if (!this.state.changeLogModalOpen) {
      return null;
    }
    return (
      <ChangeLogModal
        snippet={this.props.snippet}
        langId={this.props.langId}
        languages={this.props.languages}
        translation={this.props.translation}
        translations={this.props.translations}
        type={this.props.type}
        revertContent={this.revertContent}
        closeModal={this.closeChangeLogModal}
      />
    );
  };

  getUploadUrl = () => '/api/v2/blobs/temp';

  openUsageHistoryModal = () => {
    this.setState({
      usageHistoryModalOpen: true,
    });
  };

  closeUsageHistoryModal = () => {
    this.setState({
      usageHistoryModalOpen: false,
    });
  };

  openChangeLogModal = () => {
    this.setState({
      changeLogModalOpen: true,
    });
  };

  closeChangeLogModal = () => {
    this.setState({
      changeLogModalOpen: false,
    });
  };

  revertContent = (content, langId, type) => {
    if (type !== null) {
      this.props.changeType(type);
    }
    if (langId !== this.props.langId) {
      this.props.setLanguage(langId);
    }
    this.props.setContent(content);
    this.closeChangeLogModal();
  };

  isValid = () => this.isTitleValid() && this.isShortcutCodeValid();

  isTitleValid = () => this.props.title !== '';

  isShortcutCodeValid = () => this.props.shortcutCode.match(/^[-_a-z0-9]*$/i);

  displayMerge = (e) => {
    if (e) {
      e.preventDefault();
    }
    this.setState({
      displayMerge: !this.state.displayMerge
    });
  };

  render() {
    const {
      me,
      snippet,
      labels,
      labelsSource,
      translation,
      translations,
      languages,
      langId,
      height,
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
      splitSnippet,
      mergeSnippet,
      mergeKeepValue,
      updateMergeKeep,
      changeType,
      saving,
      removeAttachment,
    } = this.props;
    if (!snippet) {
      return null;
    }
    const canDelete = snippet.get('id') &&
      (snippet.get('person') === me.get('id') || window.DESKPRO_PERSON_PERMS['agent_snippets.delete_by_others']);
    // ratio between viewport and needed dropdowns size
    const maxHeight = height * 0.52 - 200;
    const mergeOptions = [
      { value: 'ticket', label: 'Keep ticket text' },
      { value: 'chat', label: 'Keep chat text' }
    ];
    return (
      <div id="snippets__modal">
        <Modal
          title={this.getModalTitle()}
          closeModal={closeModal}
          buttons={
            <div>
              <Button type="primary" size="large" onClick={saveSnippet} disabled={!this.isValid()} loading={saving}>
                <FormattedMessage id="agent.general.save" />
              </Button>
              <Checkbox
                checked={this.props.isDraft}
                value="is_draft"
                className="draft"
                onChange={handleChangeDraft}
              >
                <FormattedMessage id="agent.snippets.snippet_is_draft" />
              </Checkbox>
              <Button type="secondary" size="large" className="right" onClick={closeModal}>
                <FormattedMessage id="agent.general.cancel" />
              </Button>
              <ConfirmButton
                type="secondary"
                size="large"
                className="right"
                onClick={deleteSnippet}
                disabled={!canDelete}
                message={<FormattedMessage id="agent.general.are_you_sure" />}
              >
                <FormattedMessage id="agent.general.delete" />
              </ConfirmButton>
            </div>
        }
        >
          <form id="snippet_form" onSubmit={this.onSubmit}>
            <div className="title-field field">
              <Label htmlFor="snippet_title" required><FormattedMessage id="agent.general.title" /></Label>
              <Input
                id="snippet_title"
                value={title}
                onChange={handleTitle}
                required
              />
            </div>
            <div className="shortcut-field field">
              <Label htmlFor="snippet_shortcut_code">
                <FormattedMessage id="agent.snippets.shortcut_code" />
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
            <div className="labels-field field">
              <Label htmlFor="snippet_label_input"><FormattedMessage id="agent.general.labels" /></Label>
              <TagInput
                tags={labels}
                onChange={changeLabels}
                options={labelsSource}
                inputProps={{ placeholder: <FormattedMessage id="agent.general.add_a_label" /> }}
              />
            </div>
            {this.props.isSplit ?
              <div>
                <div className="type-tabs">
                  <Tabs active={this.props.type} onChange={changeType}>
                    <TabLink name="ticket">
                      <FormattedMessage id="agent.general.ticket" />
                    </TabLink>
                    <TabLink name="chat">
                      <FormattedMessage id="agent.general.chat" />
                    </TabLink>
                  </Tabs>
                </div>
                {this.state.displayMerge ?
                  <div className="merge-snippet">
                    <Select
                      value={mergeKeepValue}
                      options={mergeOptions}
                      clearable={false}
                      searchable={false}
                      onChange={updateMergeKeep}
                    />
                    <Button type="primary" size="medium" onClick={mergeSnippet}>
                      <Icon name="compress" />
                      <FormattedMessage id="agent.general.merge" />
                    </Button>
                    <Button type="secondary" size="medium" onClick={this.displayMerge}>
                      <FormattedMessage id="agent.general.cancel" />
                    </Button>
                  </div>
                 : <a href="#merge" className="merge-link" onClick={this.displayMerge}>
                   <Icon name="compress" /> Merge
                  </a>
                }
              </div>
              : null}
            <div className="editor">
              <textarea
                id="snippet__editor"
                cols="30"
                rows="10"
                defaultValue={translation.get('content', '')}
                ref={(c) => { this.textArea = c; }}
              />
              <div className="language-switch field">
                <ModalLanguageSelect
                  langId={langId}
                  languages={languages}
                  onChange={setLanguage}
                  translations={translations}
                />
              </div>
              {this.getVariables()}
            </div>
            <div className="upload">
              <div className="upload-button">
                <span className="styled-button">
                  <Icon name="paperclip" /> <FormattedMessage id="agent.general.attach_files" />
                </span>
                <UploadButton
                  id={'upload_attachment'}
                  ref={(c) => { this.uploadButton = c; }}
                  name="file"
                  onSuccess={addAttachment}
                  uploadUrl={this.getUploadUrl()}
                />
              </div>
              <span className="files">
                {translation.get('blobs').map((blobId, key) =>
                  <SnippetAttachment
                    key={key}
                    blobId={blobId}
                    removeAttachment={removeAttachment}
                  />
                )}
              </span>
            </div>
            <div className="ownership-field field">
              <Label htmlFor="snippet_ownership"><FormattedMessage id="agent.snippets.ownership" /></Label>
              <OwnershipSelectContainer
                selectedTeams={this.props.snippetTeams}
                isOwnershipGlobal={this.props.isOwnershipGlobal}
                onChange={this.props.handleTeamsChange}
                style={{ maxHeight }}
              />
            </div>
            <div className="visibility-field field">
              <Label htmlFor="snippet_visibility"><FormattedMessage id="agent.snippets.visibility" /></Label>
              <VisibilitySelectContainer
                selectedDepartments={this.props.snippetDepartments}
                isVisibleGlobal={this.props.isVisibleGlobal}
                types={this.props.types}
                onChange={this.props.handleDepartmentsChange}
                style={{ maxHeight }}
              />
            </div>
            <div className="types-field field">
              <Label htmlFor="snippet_types_input"><FormattedMessage id="agent.general.types" /></Label>
              <Checkbox
                checked={!!this.props.types.find(type => type === 'ticket')}
                value="ticket"
                onChange={handleChangeTypes}
              >
                <FormattedMessage id="agent.general.ticket" />
              </Checkbox>
              <Checkbox
                checked={!!this.props.types.find(type => type === 'chat')}
                value="chat"
                onChange={handleChangeTypes}
              >
                {<FormattedMessage id="agent.general.chat" />}
              </Checkbox>
              {!this.props.isSplit ?
                <a href="#expand" onClick={splitSnippet}><Icon name="expand" />
                  &nbsp;<FormattedMessage id="agent.snippets.split_snippet" />
                </a>
              : null}
            </div>
          </form>
        </Modal>
        {this.getUsageHistoryModal()}
        {this.getChangeLogModal()}
      </div>
    );
  }
}

@connect(state => ({
  blobs: allSnippetBlobsSelector(state)
}))
class SnippetAttachment extends React.Component {
  static propTypes = {
    blobs:            PropTypes.object,
    blobId:           PropTypes.number,
    removeAttachment: PropTypes.func,
  };

  removeAttachment = () => {
    this.props.removeAttachment(this.props.blobId);
  };

  render() {
    const { blobId, blobs } = this.props;
    if (!blobs) {
      return null;
    }
    const blob = blobs.find(b => b.get('id') === blobId);
    if (!blob) {
      return null;
    }
    return (
      <div className="snippet-attachment">
        <i className="fa fa-paperclip" />&nbsp;
        <strong>Attachment:</strong>&nbsp;
        {blob.get('filename')} ({blob.get('filesize_readable')})
        <span onClick={this.removeAttachment}>
          <Isvg
            className="close-icon"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
          />
        </span>
      </div>
    );
  }
}
