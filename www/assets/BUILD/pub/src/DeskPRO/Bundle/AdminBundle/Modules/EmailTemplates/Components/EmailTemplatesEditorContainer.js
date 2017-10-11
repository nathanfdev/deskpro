import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { fromJS } from 'immutable';
import debounce from 'lodash/debounce';
import { Select, Toggle } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { EmailsAndBlockMenuContainer } from './Menus/EmailsAndBlockMenu';
import { MediaMenuContainer } from './Menus/MediaMenu';
import { PhrasesMenuContainer } from './Menus/PhrasesMenu';
import { VariablesMenuContainer } from './Menus/VariablesMenu';
import DropDownMenu from './Menus/DropDownMenu';
import LanguageSelector from './Menus/LanguageSelector';
import * as actions from '../Actions/templatesActions';
import PreviewEmail from './PreviewEmail';
import CodeMirror from './CodeMirror';
import Editor from './Editor';
import NewCustomTemplate from './NewCustomTemplate';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
class EmailTemplatesEditorContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired,
    params:         PropTypes.object,
    route:          PropTypes.object
  };
  static contextTypes = {
    router: React.PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      saveSubmit:           false,
      undoSubmit:           false,
      resetSubmit:          false,
      previewSubmit:        false,
      addingNewTemplate:    false,
      emailAccounts:        [],
      selectedEmailAccount: '',
      previewEmailAddress:  '',
      editor:               null,
      currentWidget:        null,
    };
    this.lineWidgets = [];
  }

  componentWillMount() {
    const { dispatch } = this.props;

    const promises = [];
    dispatch(actions.setCurrentTemplateGroup('user'));
    dispatch(actions.setCurrentLanguage(window.DP_PERSON_LANG_CODE));
    promises.push(dispatch(actions.loadTemplates()));
    promises.push(dispatch(actions.loadLegacyTemplates()));
    Promise.all(promises).then(() => {
      dispatch(actions.setLegacyTemplate(this.props.emailTemplates.get('legacyTemplates').find(
        element => element.getIn([0, 'name']) === this.props.params.name
      )));
      this.findTemplate(this.props.emailTemplates.getIn(['info', 'list']), this.props.params.name);
    });
    dispatch(actions.loadPhrases(
      this.props.emailTemplates.get('currentTemplateGroup'),
      this.props.emailTemplates.get('currentLanguage')
    ));
    dispatch(actions.loadInlineImages);
    dispatch(actions.loadAttachments);
    dispatch(actions.loadEmailAccounts).then((accounts) => {
      const emailAccounts = [];
      accounts.forEach((account) => {
        emailAccounts.push({
          value: account.address,
          label: account.address
        });
      });
      this.setState({
        emailAccounts
      });
    });
  }

  componentDidMount() {
    setTimeout(() => {
      this.setState({
        editor: this.editor.editor.bodyEditor
      });
    }, 100);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.emailTemplates.get('phrases') !== this.props.emailTemplates.get('phrases')) {
      const body = this.props.emailTemplates.getIn(['template', 'template_code', 'body'], '');
      const subject = this.props.emailTemplates.getIn(['template', 'template_code', 'subject'], '');
      this.previewTemplate(body, nextProps.emailTemplates.get('currentLanguage'));

      this.props.dispatch(actions.updateTemplateBody(''));
      this.props.dispatch(actions.updateTemplateSubject(''));
      setTimeout(() => {
        this.props.dispatch(actions.updateTemplateBody(body));
        this.props.dispatch(actions.updateTemplateSubject(subject));
      }, 1);
    }
  }

  componentWillUnmount() {
    this.props.dispatch(actions.cleanState);
    if (this.state.currentWidget) {
      this.state.currentWidget.closePopup();
    }
  }

  getPhraseTranslations = phraseName => this.props.dispatch(actions.loadTranslations(phraseName));

  setCurrentWidget = (widget, editor) => {
    if (this.state.currentWidget && this.state.currentWidget !== widget) {
      this.state.currentWidget.closePopup();
    }
    if (editor) {
      this.setState({
        currentWidget: widget,
        editor
      });
    } else {
      this.setState({
        currentWidget: widget,
        editor:        this.editor.editor.bodyEditor
      });
    }
  };

  setTemplateValue = (name, code) => {
    this.props.dispatch(actions.setExtraTemplate({ name, code }));

    const body = this.props.emailTemplates.getIn(['template', 'template_code', 'body'], '');
    this.previewTemplate(body, this.props.emailTemplates.get('currentLanguage'));
  };

  findTemplate = (info, name) => {
    if (!name) {
      return false;
    }
    let found = false;
    info.forEach((type) => {
      if (!found) {
        type.get('groups').forEach((group) => {
          if (!found) {
            group.get('subGroups').forEach((subGroup) => {
              if (!found) {
                subGroup.get('templates').forEach((template) => {
                  if (template.get('name') === name || template.get('newTemplate') === name) {
                    this.props.dispatch(actions.setCurrentTemplateGroup(type.get('typeId')));
                    this.openTemplate(template);
                    found = true;
                  }
                });
              }
            });
          }
        });
      }
    });
    if (!found && name.match(/^DeskPRO:emails_custom/)) {
      this.props.dispatch(actions.setCurrentTemplateGroup('custom'));
      const newName = name.replace(/^DeskPRO:emails_custom:/, '').replace(/-/g, '_').replace(/\.html\.twig$/, '');
      const newTemplate = this.props.emailTemplates
        .getIn(['info', 'list', 'custom', 'groups', 'custom', 'subGroups', 'primary', 'templates']
      ).find(
        element => element.get('title') === `${newName}.html`
      );
      if (newTemplate) {
        this.openTemplate(newTemplate);
      } else {
        const base = 'SendmailBundle:emails_common:blank.html.twig';
        this.addTemplate(newName, base);
      }
    }
    return true;
  };

  openTemplate = (template) => {
    this.props.dispatch(actions.setCurrentTemplate(template));
    this.props.dispatch(actions.loadTemplate(template.get('newTemplate'))).then(
      (data) => {
        this.props.dispatch(actions.setTemplate(data));
      }
    );
    if (template.get('viewModel')) {
      this.props.dispatch(actions.loadVariables(template.get('viewModel')));
    } else {
      this.props.dispatch(actions.removeVariables());
    }
  };

  addTemplate = (name, baseTemplate) => {
    this.setState({
      addingNewTemplate: true
    });
    let templatePromise;
    if (baseTemplate) {
      templatePromise = new Promise((resolve) => {
        this.props.dispatch(actions.loadTemplate(baseTemplate)).then(
          (content) => {
            resolve({
              subject:    content.template_code.subject,
              body:       content.template_code.body,
              create_new: true,
            });
          }
        );
      });
    } else {
      templatePromise = new Promise((resolve) => {
        resolve({
          subject:    '',
          body:       '',
          create_new: true,
        });
      });
    }
    return templatePromise.then(template =>
        this.props.dispatch(actions.saveTemplate(`SendmailBundle:emails_custom:${name}.html.twig`, template)
      ).then(
        () => {
          this.setState({
            addingNewTemplate: false
          });
          this.props.dispatch(actions.loadTemplates()).then(
            (templates) => {
              const newTemplate = fromJS(templates).getIn(
                ['list', 'custom', 'groups', 'custom', 'subGroups', 'primary', 'templates']
              ).find(
                element => element.get('title') === `${name}.html`
              );

              this.openTemplate(newTemplate);
            }
          );
          this.selectTemplateGroup('custom');
        }
      ));
  };

  changeTemplateSubject = (value) => {
    this.props.dispatch(actions.updateTemplateSubject(value));
  };

  changeTemplateBody = (value) => {
    if (this.props.emailTemplates.getIn(['currentTemplate', 'typeId']) === 'layout') {
      this.props.dispatch(actions.updateTemplateCode(value));
    } else {
      const lang = this.props.emailTemplates.get('currentLanguage');
      this.previewTemplate(value, lang);
      this.props.dispatch(actions.updateTemplateBody(value));
    }
  };

  insertInlineImage = (file) => {
    const tag = `<img src="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="" />`;
    this.state.editor.getCodeMirror().replaceSelection(tag);
  };

  insertAttachment = (file) => {
    const tag = `<attachment id="${file.get('blob_id')}" filename="${file.get('name')}" />`;
    this.state.editor.getCodeMirror().replaceSelection(tag);
  };

  insertAttachmentAsLink = (e, file) => {
    e.stopPropagation();
    const tag = `<a href="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}">${file.get('name')}</a>`;
    this.state.editor.getCodeMirror().replaceSelection(tag);
  };

  insertPhrase = (phrase) => {
    this.state.editor.getCodeMirror().replaceSelection(phrase);
  };

  insertVariable = (variable) => {
    this.state.editor.getCodeMirror().replaceSelection(variable);
  };

  loadTemplate = name => new Promise((resolve) => {
    const template = this.props.emailTemplates.getIn(['template', 'extra_templates', name], null);
    if (template !== null) {
      resolve(template);
    } else {
      this.props.dispatch(actions.loadTemplate(name)).then((content) => {
        const code = content.template_code.code;
        this.props.dispatch(actions.setExtraTemplate({ name, code }));
        resolve(code);
      });
    }
  });

  previewTemplate = debounce(function previewDebounce(value, lang) {
    if (this.props.emailTemplates.getIn(['template', 'type']) !== 'email') {
      return true;
    }
    const variables = [];
    if (this.props.emailTemplates.get('exampleTicket')) {
      variables.push({ ticket: this.props.emailTemplates.get('exampleTicket') });
    }
    const viewModel = this.props.emailTemplates.getIn(['currentTemplate', 'viewModel']);
    const group = this.props.emailTemplates.get('currentTemplateGroup');
    const extraTemplates = this.props.emailTemplates.getIn(['template', 'extra_templates'], fromJS({})).toObject();
    const cm = this.editor.editor.bodyEditor.getCodeMirror();
    this.props.dispatch(actions.previewTemplate(viewModel, group, value, variables, lang, extraTemplates)).then(
      (payload) => {
        this.lineWidgets.forEach((lineWidget) => {
          cm.removeLineWidget(lineWidget);
        });
        this.lineWidgets = [];
        if (payload.error) {
          const msg = document.createElement('div');
          const icon = msg.appendChild(document.createElement('span'));
          icon.innerHTML = '!';
          icon.className = 'lint-error-icon';
          msg.appendChild(document.createTextNode(payload.error));
          msg.className = 'lint-error';
          this.lineWidgets.push(cm.addLineWidget(payload.line - 2, msg, { coverGutter: false, noHScroll: true }));
        } else {
          this.props.dispatch(actions.setPreview(payload));
        }
      }
    );
    return true;
  }, 400);

  resetTemplate = () => {
    this.setState({
      resetSubmit: true
    });
    const name = this.props.emailTemplates.getIn(['currentTemplate', 'newTemplate']);
    this.props.dispatch(actions.resetTemplate(name)).then(
      (payload) => {
        this.props.dispatch(actions.setTemplate(payload));
        this.props.dispatch(actions.cleanExtraTemplates());
        this.setState({
          resetSubmit: false
        });
      }
    );
  };

  resetTemplateAction = name => this.props.dispatch(actions.resetTemplate(name));

  saveTemplate = () => {
    this.setState({
      saveSubmit: true
    });
    const name = this.props.emailTemplates.getIn(['currentTemplate', 'newTemplate']);
    let template;

    if (this.props.emailTemplates.getIn(['currentTemplate', 'typeId']) === 'layout') {
      template = {
        body: this.props.emailTemplates.getIn(['template', 'template_code', 'code']),
      };
    } else {
      template = {
        subject: this.props.emailTemplates.getIn(['template', 'template_code', 'subject']),
        body:    this.props.emailTemplates.getIn(['template', 'template_code', 'body']),
      };
    }

    const promises = [];
    promises.push(this.props.dispatch(actions.saveTemplate(name, template)));

    const extraTemplates = this.props.emailTemplates.getIn(['template', 'extra_templates'], fromJS({})).toObject();

    Object.keys(extraTemplates).forEach((key) => {
      promises.push(this.props.dispatch(actions.saveTemplate(key, { body: extraTemplates[key] })));
    });
    Promise.all(promises).then(
      () => {
        this.setState({
          saveSubmit: false
        });
        this.props.dispatch(actions.cleanExtraTemplates());
        if (this.props.route.onSave) {
          this.props.route.onSave();
        }
      }
    );
  };

  selectTemplateGroup = (group) => {
    this.props.dispatch(actions.setCurrentTemplateGroup(group));
    const lang = this.props.emailTemplates.get('currentLanguage');
    this.props.dispatch(actions.loadPhrases(group, lang));
    this.props.dispatch(actions.unselectTemplate());
    this.props.dispatch(actions.deletePreview());
    this.props.dispatch(actions.setCurrentTemplate(null));
  };

  undoChanges = () => new Promise((resolve) => {
    this.setState({
      undoSubmit: true
    });
    this.props.dispatch(actions.loadTemplate(this.props.emailTemplates.getIn(['currentTemplate', 'newTemplate']))).then(
      () => {
        this.setState({
          undoSubmit: false
        });
        this.props.dispatch(actions.cleanExtraTemplates());

        const body = this.props.emailTemplates.getIn(['template', 'template_code', 'body'], '');
        this.previewTemplate(body, this.props.emailTemplates.get('currentLanguage'));
        resolve();
      }
    );
  });

  sendPreview = () => {
    this.setState({
      previewSubmit: true
    });
    const variables = [];
    if (this.props.emailTemplates.get('exampleTicket')) {
      variables.push({ ticket: this.props.emailTemplates.get('exampleTicket') });
    }
    const viewModel = this.props.emailTemplates.getIn(['currentTemplate', 'viewModel']);
    const group = this.props.emailTemplates.get('currentTemplateGroup');
    const lang = this.props.emailTemplates.get('currentLanguage');
    const subject = this.props.emailTemplates.getIn(['template', 'template_code', 'subject']);
    const body = this.props.emailTemplates.getIn(['template', 'template_code', 'body']);
    const from = this.state.selectedEmailAccount;
    const to   = this.state.previewEmailAddress;
    const extraTemplates = this.props.emailTemplates.getIn(['template', 'extra_templates'], fromJS({})).toObject();
    this.props.dispatch(actions.sendPreview(viewModel, group, subject, body, variables, lang, from, to, extraTemplates))
      .then(
        () => {
          this.setState({
            previewSubmit: false
          });
        }
      );
  };

  savePhraseTranslations = (phraseName, translations) =>
    this.props.dispatch(actions.saveTranslations(phraseName, translations))
      .then(() => {
        const body = this.props.emailTemplates.getIn(['template', 'template_code', 'body'], '');
        this.previewTemplate(body, this.props.emailTemplates.get('currentLanguage'));
        this.props.dispatch(actions.loadPhrases(
          this.props.emailTemplates.get('currentTemplateGroup'),
          this.props.emailTemplates.get('currentLanguage')
        ));
      });

  selectEmailAccount = (account) => {
    this.setState({
      selectedEmailAccount: account
    });
  };

  handleEmailAddress = (e) => {
    this.setState({
      previewEmailAddress: e.target.value
    });
  };

  render() {
    return (<EmailTemplatesEditor
      emailTemplates={this.props.emailTemplates}
      emailAccounts={this.state.emailAccounts}
      name={this.props.params.name}
      selectEmailAccount={this.selectEmailAccount}
      selectedEmailAccount={this.state.selectedEmailAccount}
      previewEmailAddress={this.state.previewEmailAddress}
      selectTemplateGroup={this.selectTemplateGroup}
      handleEmailAddress={this.handleEmailAddress}
      changeTemplateBody={this.changeTemplateBody}
      changeTemplateSubject={this.changeTemplateSubject}
      saveTemplate={this.saveTemplate}
      resetTemplate={this.resetTemplate}
      resetTemplateAction={this.resetTemplateAction}
      undoChanges={this.undoChanges}
      insertInlineImage={this.insertInlineImage}
      insertAttachment={this.insertAttachment}
      insertAttachmentAsLink={this.insertAttachmentAsLink}
      insertPhrase={this.insertPhrase}
      insertVariable={this.insertVariable}
      sendPreview={this.sendPreview}
      addTemplate={this.addTemplate}
      loadTemplate={this.loadTemplate}
      setCurrentWidget={this.setCurrentWidget}
      setTemplateValue={this.setTemplateValue}
      getPhraseTranslations={this.getPhraseTranslations}
      savePhraseTranslations={this.savePhraseTranslations}
      previewSubmit={this.state.previewSubmit}
      resetSubmit={this.state.resetSubmit}
      saveSubmit={this.state.saveSubmit}
      undoSubmit={this.state.undoSubmit}
      addingNewTemplate={this.state.addingNewTemplate}
      ref={(c) => { this.editor = c; }}
    />);
  }
}

class EmailTemplatesEditor extends React.Component {
  static propTypes = {
    emailTemplates:         PropTypes.object,
    selectedEmailAccount:   PropTypes.string,
    previewEmailAddress:    PropTypes.string,
    name:                   PropTypes.string,
    emailAccounts:          PropTypes.array,
    selectEmailAccount:     PropTypes.func,
    handleEmailAddress:     PropTypes.func,
    selectTemplateGroup:    PropTypes.func,
    changeTemplateSubject:  PropTypes.func,
    changeTemplateBody:     PropTypes.func,
    saveTemplate:           PropTypes.func,
    resetTemplate:          PropTypes.func,
    resetTemplateAction:    PropTypes.func,
    undoChanges:            PropTypes.func,
    insertAttachment:       PropTypes.func,
    insertAttachmentAsLink: PropTypes.func,
    insertInlineImage:      PropTypes.func,
    insertPhrase:           PropTypes.func,
    insertVariable:         PropTypes.func,
    sendPreview:            PropTypes.func,
    addTemplate:            PropTypes.func,
    loadTemplate:           PropTypes.func,
    setTemplateValue:       PropTypes.func,
    setCurrentWidget:       PropTypes.func,
    getPhraseTranslations:  PropTypes.func,
    savePhraseTranslations: PropTypes.func,
    previewSubmit:          PropTypes.bool,
    resetSubmit:            PropTypes.bool,
    saveSubmit:             PropTypes.bool,
    undoSubmit:             PropTypes.bool,
    addingNewTemplate:      PropTypes.bool,
  };

  constructor(props) {
    super(props);
    this.state = {
      currentTemplate:         'Select a template',
      templateSubject:         '',
      templateBody:            '',
      templateType:            '',
      templatesGroups:         [],
      contentChanged:          false,
      textareaDisabled:        true,
      newCustomTemplateOpened: false,
      showLegacy:              false,
    };
  }

  componentDidMount() {
    this.compileProps(this.props.emailTemplates);
  }

  componentWillReceiveProps(nextProps) {
    this.compileProps(nextProps.emailTemplates);
  }

  setTemplateValue = (name, code) => {
    this.props.setTemplateValue(name, code);
    this.checkChanges();
  };

  compileProps = (emailTemplates) => {
    const templatesGroups = [];
    if (emailTemplates && emailTemplates.getIn(['info', 'list'])) {
      emailTemplates.getIn(['info', 'list']).valueSeq()
        .filter((group) => {
          if (group.get('typeId') === 'layout') {
            return false;
          }
          if (group.get('typeId') === 'custom') {
            return group
                .get('groups')
                .get('custom')
                .get('subGroups')
                .get('primary')
                .get('templates').size > 0;
          }
          return true;
        })
        .forEach((group) => {
          templatesGroups.push({
            value: group.get('typeId'),
            label: group.get('title')
          });
        });
    }
    this.setState({
      templatesGroups
    });

    this.setState({
      currentTemplate: emailTemplates.getIn(['currentTemplate', 'title'], 'Select a template')
    });

    if (this.props.emailTemplates.getIn(['currentTemplate', 'typeId']) === 'layout') {
      this.setState({
        templateSubject:  '',
        templateType:     'block',
        templateBody:     emailTemplates.getIn(['template', 'template_code', 'code'], ''),
        textareaDisabled: !emailTemplates.get('currentTemplate')
      });
    } else {
      this.setState({
        templateSubject:  emailTemplates.getIn(['template', 'template_code', 'subject'], ''),
        templateType:     'email',
        templateBody:     emailTemplates.getIn(['template', 'template_code', 'body'], ''),
        textareaDisabled: !emailTemplates.get('currentTemplate')
      });
    }
  };

  handleChangeBody = (value) => {
    if (this.props.emailTemplates.get('template')) {
      this.props.changeTemplateBody(value);
      this.checkChanges();
    }
  };

  handleChangeSubject = (value) => {
    this.props.changeTemplateSubject(value);
    this.checkChanges();
  };

  handleSelectTemplateGroup = (value) => {
    if (!this.state.contentChanged || confirm('Changes on the current template will be overwritten')) {
      this.props.selectTemplateGroup(value);
    }
    return false;
  };

  handlePreviewToggle = (value) => {
    this.setState({
      showLegacy: value
    });
  };

  closeMediaMenu = () => {
    this.mediaMenu.closeMenu();
  };

  closePhrasesMenu = () => {
    this.phrasesMenu.closeMenu();
  };

  closeTemplateMenu = () => {
    this.templateMenu.closeMenu();
  };

  closeVariablesMenu = () => {
    this.variablesMenu.closeMenu();
  };

  openNewTemplateDialog = () => {
    if (!this.state.contentChanged || confirm('Changes on the current template will be overwritten')) {
      this.setState({
        newCustomTemplateOpened: true
      });
    }
  };

  openLegacyTemplatesEditor = () => {
    window.location.href = 'admin-interface#/tickets/email_templates';
  };

  closeNewTemplateDialog = () => {
    this.setState({
      newCustomTemplateOpened: false
    });
  };

  checkChanges = () => {
    const body = this.props.emailTemplates.getIn(['template', 'original_code', 'body'], '');
    const subject = this.props.emailTemplates.getIn(['template', 'original_code', 'subject'], '');
    const extraTemplates = this.props.emailTemplates.getIn(['template', 'extra_templates'], fromJS({}));


    if (subject !== this.state.templateSubject || body !== this.state.templateBody || extraTemplates.size) {
      this.setState({
        contentChanged: true
      });
    } else {
      this.setState({
        contentChanged: false
      });
    }
  };

  undoChanges = () => {
    this.props.undoChanges().then(() => {
      this.checkChanges();
    });
  };

  render() {
    return (
      <div className="dp-email-templates">
        <div className="editor">
          <div className="header">
            <div>
              <div className="ui form">
                <div className="ui field">
                  <Select
                    options={this.state.templatesGroups}
                    className="group-select basic"
                    onChange={this.handleSelectTemplateGroup}
                    value={this.props.emailTemplates.get('currentTemplateGroup')}
                  />
                </div>
              </div>
              <LanguageSelector languages={window.DP_ENABLED_LANGS} />
              <Button
                className="right floated"
                onClick={this.openNewTemplateDialog}
              >
                + New Template
              </Button>
              {this.props.emailTemplates.get('legacyTemplates').size ?
                <Button
                  className="right basic small floated"
                  onClick={this.openLegacyTemplatesEditor}
                >
                  Upgrade Legacy Templates
                </Button> : null
              }
              <NewCustomTemplate
                opened={this.state.newCustomTemplateOpened}
                addingNewTemplate={this.props.addingNewTemplate}
                close={this.closeNewTemplateDialog}
                addTemplate={this.props.addTemplate}
              />
            </div>
            <div>
              <div className="top-menu">
                <DropDownMenu
                  icon="mail"
                  label={this.state.currentTemplate}
                  className="emails-block-button"
                  ref={(c) => { this.templateMenu = c; }}
                >
                  <EmailsAndBlockMenuContainer
                    closeMenu={this.closeTemplateMenu}
                  />
                </DropDownMenu>
              </div>
              <div className={classNames('top-menu right floated', { disabled: this.state.textareaDisabled })}>
                <DropDownMenu
                  icon="image"
                  label="Media"
                  className="media-button"
                  disabled={this.state.textareaDisabled}
                  ref={(c) => { this.mediaMenu = c; }}
                >
                  <MediaMenuContainer
                    closeMenu={this.closeMediaMenu}
                    insertAttachment={this.props.insertAttachment}
                    insertAttachmentAsLink={this.props.insertAttachmentAsLink}
                    insertInlineImage={this.props.insertInlineImage}
                  />
                </DropDownMenu>
              </div>
              <div className={classNames('top-menu right floated', { disabled: this.state.textareaDisabled })}>
                <DropDownMenu
                  icon="globe"
                  label="Phrases"
                  className="phrases-button"
                  disabled={this.state.textareaDisabled}
                  ref={(c) => { this.phrasesMenu = c; }}
                >
                  <PhrasesMenuContainer
                    closeMenu={this.closePhrasesMenu}
                    languages={window.DP_ENABLED_LANGS}
                    insertPhrase={this.props.insertPhrase}
                  />
                </DropDownMenu>
              </div>
              <div
                className={classNames(
                  'top-menu right floated',
                  { disabled: !this.props.emailTemplates.get('variables') }
                )}
              >
                <DropDownMenu
                  icon="dollar"
                  label="Variables"
                  className="variables-button"
                  disabled={!this.props.emailTemplates.get('variables')}
                  ref={(c) => { this.variablesMenu = c; }}
                >
                  <VariablesMenuContainer
                    closeMenu={this.closeVariablesMenu}
                    insertVariable={this.props.insertVariable}
                  />
                </DropDownMenu>
              </div>
            </div>
          </div>
          <Editor
            disabled={this.state.textareaDisabled}
            body={this.state.templateBody}
            subject={this.state.templateSubject}
            type={this.state.templateType}
            changeTemplateSubject={this.handleChangeSubject}
            changeTemplateBody={this.handleChangeBody}
            ref={(c) => { this.editor = c; }}
            phrases={this.props.emailTemplates.get('phrases')}
            getPhraseTranslations={this.props.getPhraseTranslations}
            loadTemplate={this.props.loadTemplate}
            resetTemplate={this.props.resetTemplateAction}
            savePhraseTranslations={this.props.savePhraseTranslations}
            setCurrentWidget={this.props.setCurrentWidget}
            setTemplateValue={this.setTemplateValue}
          />
          <div className="footer">
            <Button
              className={classNames('primary small', { loading: this.props.saveSubmit })}
              disabled={this.state.textareaDisabled || !this.state.contentChanged}
              onClick={this.props.saveTemplate}
            >
              Save changes
            </Button>
            <Button
              className={classNames('basic small', { loading: this.props.undoSubmit })}
              disabled={this.state.textareaDisabled || !this.state.contentChanged}
              onClick={this.undoChanges}
              confirm
            >
              Undo changes
            </Button>
            <Button
              className={classNames('right floated basic small', { loading: this.props.resetSubmit })}
              disabled={this.state.textareaDisabled}
              onClick={this.props.resetTemplate}
              confirm
            >
              Reset template
            </Button>
          </div>
        </div>
        <div className="preview">
          <div className="header">
            {this.props.emailTemplates.get('legacyTemplate') ?
              <div>
                <p>
                  Please copy your changes into the new template on the left side, you can switch to the preview to see how your email will look
                </p>
                <Toggle onChange={this.handlePreviewToggle} active={this.state.showLegacy}>Preview</Toggle>
              </div>
              :
              <div>
                <p>
                  Enter details below to send a test email to yourself or a colleague.
                </p>
                <form className="ui form">
                  <div className="fields">
                    <div className="six wide field">
                      <label htmlFor="test_email_from">From</label>
                      <Select
                        options={this.props.emailAccounts}
                        className="email-account basic"
                        onChange={this.props.selectEmailAccount}
                        disabled={this.state.textareaDisabled || this.state.templateType === 'block'}
                        value={this.props.selectedEmailAccount}
                      />
                    </div>
                    <div className="six wide field">
                      <label htmlFor="test_email_to">To</label>
                      <input
                        type="text"
                        name="to"
                        id="test_email_to"
                        onChange={this.props.handleEmailAddress}
                        disabled={this.state.textareaDisabled || this.state.templateType === 'block'}
                        value={this.props.previewEmailAddress}
                      />
                    </div>
                    <div className="four wide field">
                      <label htmlFor="test_email_submit">&nbsp;</label>
                      <Button
                        className={classNames('ui basic button', { loading: this.props.previewSubmit })}
                        disabled={this.state.textareaDisabled || this.state.templateType === 'block'}
                        onClick={this.props.sendPreview}
                      >
                        Send
                      </Button>
                    </div>
                  </div>
                </form>
              </div>
            }
          </div>
          {this.props.emailTemplates.get('legacyTemplate') && !this.state.showLegacy ?
            <div>
              <CodeMirror value={this.props.emailTemplates.getIn(['legacyTemplate', 0, 'template_code'])} />
            </div>
            :
            <PreviewEmail
              preview={this.props.emailTemplates.get('preview')}
              type={this.state.templateType}
              content={this.state.templateBody}
              name={this.props.name}
            />
          }
        </div>
      </div>
    );
  }
}
export default EmailTemplatesEditorContainer;
