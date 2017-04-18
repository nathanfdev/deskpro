import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import debounce from 'lodash/function/debounce';
import { Select } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { EmailsAndBlockMenuContainer } from './Menus/EmailsAndBlockMenu';
import { MediaMenuContainer } from './Menus/MediaMenu';
import { PhrasesMenuContainer } from './Menus/PhrasesMenu';
import { VariablesMenuContainer } from './Menus/VariablesMenu';
import DropDownMenu from './Menus/DropDownMenu';
import LanguageSelector from './Menus/LanguageSelector';
import * as actions from '../Actions/templatesActions';
import PreviewEmail from './PreviewEmail';
import Editor from './Editor';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
class EmailTemplatesEditorContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      saveSubmit:  false,
      undoSubmit:  false,
      resetSubmit: false,
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;

    dispatch(actions.loadTemplates());
    this.props.dispatch(actions.loadPhrases(
      this.props.emailTemplates.get('currentTemplateGroup'),
      this.props.emailTemplates.get('currentLanguage')
    ));
    this.props.dispatch(actions.loadInlineImages);
    this.props.dispatch(actions.loadAttachments);
  }

  componentDidMount() {

  }

  componentWillUnmount() {
    this.props.dispatch(actions.cleanState);
  }

  selectTemplateGroup = (group) => {
    this.props.dispatch(actions.setCurrentTemplateGroup(group));
    const lang = this.props.emailTemplates.get('currentLanguage');
    this.props.dispatch(actions.loadPhrases(group, lang));
  };

  changeTemplateSubject = (event) => {
    this.props.dispatch(actions.updateTemplateSubject(event.target.value));
  };

  changeTemplateBody = (value) => {
    const variables = [];
    if (this.props.emailTemplates.get('exampleTicket')) {
      variables.push({ ticket: this.props.emailTemplates.get('exampleTicket') });
    }
    const viewModel = this.props.emailTemplates.get('currentTemplate').get('viewModel');
    const group = this.props.emailTemplates.get('currentTemplateGroup');
    this.previewTemplate(viewModel, group, value, variables);
    this.props.dispatch(actions.updateTemplateBody(value));
  };

  previewTemplate = debounce(function (viewModel, group, value, variables) {
    this.props.dispatch(actions.previewTemplate(viewModel, group, value, variables));
  }, 400);

  saveTemplate = () => {
    this.setState({
      saveSubmit: true
    });
    const name = this.props.emailTemplates.get('currentTemplate').get('newTemplate');
    const template = {
      subject: this.props.emailTemplates.get('template').get('template_code').get('subject'),
      body:    this.props.emailTemplates.get('template').get('template_code').get('body'),
    };
    this.props.dispatch(actions.saveTemplate(name, template)).then(
      () => {
        this.setState({
          saveSubmit: false
        });
      }
    );
  };

  resetTemplate = () => {
    this.setState({
      resetSubmit: true
    });
    const name = this.props.emailTemplates.get('currentTemplate').get('newTemplate');
    this.props.dispatch(actions.resetTemplate(name)).then(
      () => {
        this.setState({
          resetSubmit: false
        });
      }
    );
  };

  undoChanges = () => {
    this.setState({
      undoSubmit: true
    });
    this.props.dispatch(actions.loadTemplate(this.props.emailTemplates.get('currentTemplate').get('newTemplate'))).then(
      () => {
        this.setState({
          undoSubmit: false
        });
      }
    );
  };

  insertInlineImage = (file) => {
    const tag = `<img src="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="" />`;
    this.editor.editor.bodyEditor.codeMirror.replaceSelection(tag);
  };

  insertAttachment = (file) => {
    const tag = `<attachment id="${file.get('blob_id')}" filename="${file.get('name')}" />`;
    this.editor.editor.bodyEditor.codeMirror.replaceSelection(tag);
  };

  insertAttachmentAsLink = (e, file) => {
    e.stopPropagation();
    const tag = `<a href="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="">${file.get('name')}</a>`;
    this.editor.editor.bodyEditor.codeMirror.replaceSelection(tag);
  };

  insertPhrase = (phrase) => {
    this.editor.editor.bodyEditor.codeMirror.replaceSelection(phrase);
  };

  insertVariable = (variable) => {
    this.editor.editor.bodyEditor.codeMirror.replaceSelection(variable);
  };

  render() {
    return (<EmailTemplatesEditor
      emailTemplates={this.props.emailTemplates}
      selectTemplateGroup={this.selectTemplateGroup}
      changeTemplateBody={this.changeTemplateBody}
      changeTemplateSubject={this.changeTemplateSubject}
      saveTemplate={this.saveTemplate}
      resetTemplate={this.resetTemplate}
      undoChanges={this.undoChanges}
      insertInlineImage={this.insertInlineImage}
      insertAttachment={this.insertAttachment}
      insertAttachmentAsLink={this.insertAttachmentAsLink}
      insertPhrase={this.insertPhrase}
      insertVariable={this.insertVariable}
      resetSubmit={this.state.resetSubmit}
      saveSubmit={this.state.saveSubmit}
      undoSubmit={this.state.undoSubmit}
      ref={(c) => { this.editor = c; }}
    />);
  }
}

class EmailTemplatesEditor extends React.Component {
  static propTypes = {
    emailTemplates:         PropTypes.object,
    selectTemplateGroup:    PropTypes.func,
    changeTemplateSubject:  PropTypes.func,
    changeTemplateBody:     PropTypes.func,
    saveTemplate:           PropTypes.func,
    resetTemplate:          PropTypes.func,
    undoChanges:            PropTypes.func,
    insertAttachment:       PropTypes.func,
    insertAttachmentAsLink: PropTypes.func,
    insertInlineImage:      PropTypes.func,
    insertPhrase:           PropTypes.func,
    insertVariable:         PropTypes.func,
    resetSubmit:            PropTypes.bool,
    saveSubmit:             PropTypes.bool,
    undoSubmit:             PropTypes.bool,
  };

  constructor(props) {
    super(props);
    this.state = {
      currentTemplate:  'Select a template',
      templateSubject:  '',
      templateBody:     '',
      templatesGroups:  [],
      textareaDisabled: true,
    };
  }

  componentDidMount() {
    this.compileProps(this.props.emailTemplates);
  }

  componentWillReceiveProps(nextProps) {
    this.compileProps(nextProps.emailTemplates);
  }

  compileProps = (emailTemplates) => {
    const templatesGroups = [];
    if (emailTemplates && emailTemplates.get('info').get('list')) {
      emailTemplates.get('info').get('list').valueSeq().forEach((group) => {
        templatesGroups.push({
          value: group.get('typeId'),
          label: group.get('title')
        });
      });
    }
    this.setState({
      templatesGroups
    });

    if (emailTemplates.get('currentTemplate')) {
      this.setState({
        currentTemplate: emailTemplates.get('currentTemplate').get('title')
      });
    }

    if (emailTemplates.get('template') && emailTemplates.get('template').get('template_code')) {
      this.setState({
        templateSubject:  emailTemplates.get('template').get('template_code').get('subject'),
        templateBody:     emailTemplates.get('template').get('template_code').get('body'),
        textareaDisabled: false
      });
    } else {
      this.setState({
        templateSubject:  '',
        templateBody:     '',
        textareaDisabled: true
      });
    }
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
                    onChange={this.props.selectTemplateGroup}
                    value={this.props.emailTemplates.get('currentTemplateGroup')}
                  />
                </div>
              </div>
              <LanguageSelector languages={window.DP_ENABLED_LANGS} />
              <button className="ui right floated button">
                + New Template
              </button>
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
              <div className={classNames('top-menu right floated', { disabled: !this.props.emailTemplates.get('variables') })}>
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
            changeTemplateSubject={this.props.changeTemplateSubject}
            changeTemplateBody={this.props.changeTemplateBody}
            ref={(c) => { this.editor = c; }}
          />
          <div className="footer">
            <Button
              className={classNames('primary small', { loading: this.props.saveSubmit })}
              disabled={this.state.textareaDisabled}
              onClick={this.props.saveTemplate}
            >
              Save changes
            </Button>
            <Button
              className={classNames('basic small', { loading: this.props.undoSubmit })}
              disabled={this.state.textareaDisabled}
              onClick={this.props.undoChanges}
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
            <p>
              Enter details below to send a test email to yourself or a colleague.
            </p>
            <form className="ui form">
              <div className="fields">
                <div className="six wide field">
                  <label htmlFor="test_email_from">From</label>
                  <div className="ui selection dropdown" id="test_email_from">
                    <input type="hidden" />
                    <i className="dropdown icon" />
                    <span className="item">support@deskpro.com</span>
                  </div>
                </div>
                <div className="six wide field">
                  <label htmlFor="test_email_to">To</label>
                  <input type="text" name="to" id="test_email_to" />
                </div>
                <div className="four wide field">
                  <label htmlFor="test_email_submit">&nbsp;</label>
                  <input type="submit" className="ui basic button" value="Send" id="test_email_submit" />
                </div>
              </div>
            </form>
          </div>
          <PreviewEmail preview={this.props.emailTemplates.get('preview')} />
        </div>
      </div>
    );
  }
}
export default EmailTemplatesEditorContainer;
