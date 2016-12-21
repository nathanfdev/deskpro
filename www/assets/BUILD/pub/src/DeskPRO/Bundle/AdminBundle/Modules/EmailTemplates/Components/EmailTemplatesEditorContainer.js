import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
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
    this.props.dispatch(actions.previewTemplate(viewModel, value, variables));
    this.props.dispatch(actions.updateTemplateBody(value));
  };

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
    const tag = `<img src="{{ path('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="" />`;
    const body = this.props.emailTemplates.get('template').get('template_code').get('body') + tag;
    this.changeTemplateBody(body);
  };

  insertAttachment = (file) => {
    const tag = `<attachment id="${file.get('blob_id')}" filename="${file.get('name')}" />`;
    const body = this.props.emailTemplates.get('template').get('template_code').get('body') + tag;
    this.changeTemplateBody(body);
  };

  insertAttachmentAsLink = (e, file) => {
    e.stopPropagation();
    const tag = `<a href="{{ path('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="">${file.get('name')}</a>`;
    const body = this.props.emailTemplates.get('template').get('template_code').get('body') + tag;
    this.changeTemplateBody(body);
  };

  render() {
    return (<EmailTemplatesEditor
      emailTemplates={this.props.emailTemplates}
      selectTemplateGroup={this.selectTemplateGroup}
      changeTemplateBody={this.changeTemplateBody}
      saveTemplate={this.saveTemplate}
      resetTemplate={this.resetTemplate}
      undoChanges={this.undoChanges}
      insertInlineImage={this.insertInlineImage}
      insertAttachment={this.insertAttachment}
      insertAttachmentAsLink={this.insertAttachmentAsLink}
      resetSubmit={this.state.resetSubmit}
      saveSubmit={this.state.saveSubmit}
      undoSubmit={this.state.undoSubmit}
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
    resetSubmit:            PropTypes.bool,
    saveSubmit:             PropTypes.bool,
    undoSubmit:             PropTypes.bool,
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
    const emailTemplates = this.props.emailTemplates;
    const templatesGroups = [];
    if (emailTemplates && emailTemplates.get('info').get('list')) {
      emailTemplates.get('info').get('list').valueSeq().forEach((group) => {
        templatesGroups.push({
          value: group.get('typeId'),
          label: group.get('title')
        });
      });
    }
    let currentTemplate = 'Select a template';
    if (emailTemplates.get('currentTemplate')) {
      currentTemplate = emailTemplates.get('currentTemplate').get('title');
    }
    let templateSubject = '';
    let templateBody = '';
    let textareaDisabled = true;
    if (emailTemplates.get('template') && emailTemplates.get('template').get('template_code')) {
      templateSubject = emailTemplates.get('template').get('template_code').get('subject');
      templateBody = emailTemplates.get('template').get('template_code').get('body');
      textareaDisabled = false;
    }
    return (
      <div className="dp-email-templates">
        <div className="editor">
          <div className="header">
            <div>
              <div className="ui form">
                <div className="ui field">
                  <Select
                    options={templatesGroups}
                    className="group-select basic"
                    onChange={this.props.selectTemplateGroup}
                    value={emailTemplates.get('currentTemplateGroup')}
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
                  label={currentTemplate}
                  className="emails-block-button"
                  ref={(c) => { this.templateMenu = c; }}
                >
                  <EmailsAndBlockMenuContainer
                    closeMenu={this.closeTemplateMenu}
                  />
                </DropDownMenu>
              </div>
              <div className={classNames('top-menu right floated', { disabled: textareaDisabled })}>
                <DropDownMenu
                  icon="image"
                  label="Media"
                  className="media-button"
                  disabled={textareaDisabled}
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
              <div className={classNames('top-menu right floated', { disabled: textareaDisabled })}>
                <DropDownMenu
                  icon="globe"
                  label="Phrases"
                  className="phrases-button"
                  disabled={textareaDisabled}
                  ref={(c) => { this.phrasesMenu = c; }}
                >
                  <PhrasesMenuContainer
                    closeMenu={this.closePhrasesMenu}
                    languages={window.DP_ENABLED_LANGS}
                  />
                </DropDownMenu>
              </div>
              <div className={classNames('top-menu right floated', { disabled: !emailTemplates.get('variables') })}>
                <DropDownMenu
                  icon="dollar"
                  label="Variables"
                  className="variables-button"
                  disabled={!emailTemplates.get('variables')}
                  ref={(c) => { this.variablesMenu = c; }}
                >
                  <VariablesMenuContainer
                    closeMenu={this.closeVariablesMenu}
                  />
                </DropDownMenu>
              </div>
            </div>
          </div>
          <div className="dp-code-editor">
            <div className={classNames('ui dimmer inverted', { active: textareaDisabled })}>
              <div className="ui big loader text">Please select a template to edit</div>
            </div>
            Email subject:
            <textarea
              className={classNames('email-subject', { disabled: textareaDisabled })}
              rows="2"
              value={templateSubject}
              disabled={textareaDisabled}
              onChange={this.props.changeTemplateSubject}
            />
            Email:
            <textarea
              className={classNames('email-body', { disabled: textareaDisabled })}
              rows="20"
              value={templateBody}
              disabled={textareaDisabled}
              onChange={e => this.props.changeTemplateBody(e.target.value)}
            />
          </div>
          <div className="footer">
            <Button
              className={classNames('primary small', { loading: this.props.saveSubmit })}
              disabled={textareaDisabled}
              onClick={this.props.saveTemplate}
            >
              Save changes
            </Button>
            <Button
              className={classNames('basic small', { loading: this.props.undoSubmit })}
              disabled={textareaDisabled}
              onClick={this.props.undoChanges}
              confirm
            >
              Undo changes
            </Button>
            <Button
              className={classNames('right floated basic small', { loading: this.props.resetSubmit })}
              disabled={textareaDisabled}
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
          <PreviewEmail preview={emailTemplates.get('preview')} />
        </div>
      </div>
    );
  }
}
export default EmailTemplatesEditorContainer;
