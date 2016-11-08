import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Select } from 'DeskPRO/Component/Semantic/Form';
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

  componentWillMount() {
    const { dispatch } = this.props;

    dispatch(actions.loadTemplates());
    this.props.dispatch(actions.loadPhrases(
      this.props.emailTemplates.get('currentTemplateGroup'),
      this.props.emailTemplates.get('currentLanguage')
    ));
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
            <textarea className="email-subject" rows="2" />
            Email:
            <textarea className="email-body" rows="20" />
          </div>
          <div className="footer">
            <button className="ui primary small button">Save changes</button>
            <button className="ui basic small button">Undo changes</button>
            <button className="ui right floated basic small button">Reset template</button>
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
