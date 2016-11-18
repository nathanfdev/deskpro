import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Select } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { EmailsAndBlockMenuContainer } from './Menus/EmailsAndBlockMenu';
import { PhrasesMenuContainer } from './Menus/PhrasesMenu';
import { VariablesMenuContainer } from './Menus/VariablesMenu';
import DropDownMenu from './Menus/DropDownMenu';
import LanguageSelector from './Menus/LanguageSelector';
import * as actions from '../Actions/templatesActions';

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
      resetSubmit: false
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;

    dispatch(actions.loadTemplates());
    this.props.dispatch(actions.loadPhrases(
      this.props.emailTemplates.get('currentTemplateGroup'),
      this.props.emailTemplates.get('currentLanguage')
    ));
  }

  componentWillUnmount() {
    this.props.dispatch(actions.cleanState);
  }

  closeTemplateMenu = () => {
    this.templateMenu.closeMenu();
  };

  closeVariablesMenu = () => {
    this.variablesMenu.closeMenu();
  };

  closePhrasesMenu = () => {
    this.phrasesMenu.closeMenu();
  };

  selectTemplateGroup = (group) => {
    this.props.dispatch(actions.setCurrentTemplateGroup(group));
    const lang = this.props.emailTemplates.get('currentLanguage');
    this.props.dispatch(actions.loadPhrases(group, lang));
  };

  changeTemplateSubject = (event) => {
    this.props.dispatch(actions.updateTemplateSubject(event.target.value));
  };

  changeTemplateBody = (event) => {
    this.props.dispatch(actions.updateTemplateBody(event.target.value));
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
    const name = this.props.emailTemplates.get('currentTemplate').get('name');
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
    this.props.dispatch(actions.loadTemplate(this.props.emailTemplates.get('currentTemplate').get('name'))).then(
      () => {
        this.setState({
          undoSubmit: false
        });
      }
    );
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
                    onChange={this.selectTemplateGroup}
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
                  ref={(c) => { this.templateMenu = c; }}
                >
                  <EmailsAndBlockMenuContainer
                    closeMenu={this.closeTemplateMenu}
                  />
                </DropDownMenu>
              </div>
              <div className="top-menu right floated">
                <div>
                  <i className="icon image" />
                  Media
                  <i className="fa fa-caret-down" />
                </div>
              </div>
              <div className={classNames('top-menu right floated', { disabled: !emailTemplates.get('phrases') })}>
                <DropDownMenu
                  icon="globe"
                  label="Phrases"
                  ref={(c) => { this.phrasesMenu = c; }}
                >
                  <PhrasesMenuContainer
                    closeMenu={this.closePhrasesMenu}
                  />
                </DropDownMenu>
              </div>
              <div className={classNames('top-menu right floated', { disabled: !emailTemplates.get('variables') })}>
                <DropDownMenu
                  icon="dollar"
                  label="Variables"
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
            Email subject:
            <textarea
              className={classNames('email-subject', { disabled: textareaDisabled })}
              rows="2"
              value={templateSubject}
              disabled={textareaDisabled}
              onChange={this.changeTemplateSubject}
            />
            Email:
            <textarea
              className={classNames('email-body', { disabled: textareaDisabled })}
              rows="20"
              value={templateBody}
              disabled={textareaDisabled}
              onChange={this.changeTemplateBody}
            />
          </div>
          <div className="footer">
            <Button
              className={classNames('primary small', { loading: this.state.saveSubmit })}
              disabled={textareaDisabled}
              onClick={this.saveTemplate}
            >
              Save changes
            </Button>
            <Button
              className={classNames('basic small', { loading: this.state.undoSubmit })}
              disabled={textareaDisabled}
              onClick={this.undoChanges}
              confirm
            >
              Undo changes
            </Button>
            <Button
              className={classNames('right floated basic small', { loading: this.state.resetSubmit })}
              disabled={textareaDisabled}
              onClick={this.resetTemplate}
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
          <div className="email-preview">
            <div className="email">
              Test preview email
            </div>
          </div>
        </div>
      </div>
    );
  }
}
export default EmailTemplatesEditorContainer;
