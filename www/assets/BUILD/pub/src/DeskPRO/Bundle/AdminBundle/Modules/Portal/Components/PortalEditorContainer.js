import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';
import { Button, ConfirmButton, Icon } from '@deskpro/react-components';
import Editor from 'DeskPRO/Component/CMEditor/Editor';
import DropDownMenu from 'DeskPRO/Component/CMEditor/Menus/DropDownMenu';
import { TemplatesMenuContainer } from './Menus/TemplatesMenu';
import * as actions from '../Actions/templatesActions';

@connect(state => ({
  portalEditor: state.Portal.templates
}))
class PortalEditorContainer extends React.Component {
  static propTypes = {
    dispatch:     PropTypes.func,
    portalEditor: PropTypes.object,
    params:       PropTypes.object,
  };
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      currentWidget: null,
    };
  }


  componentWillMount() {
    const { dispatch } = this.props;

    dispatch(actions.updateTemplateCode(''));

    dispatch(actions.loadTemplates());

    if (this.props.params.name) {
      dispatch(actions.loadTemplate(this.props.params.name)).then((value) => {
        dispatch(actions.updateTemplateCode(value));
      });
    }
    dispatch(actions.loadPhrases(
      'user',
      this.props.portalEditor.get('currentLanguage')
    ));
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

  loadTemplate = name => new Promise((resolve) => {
    const template = this.props.portalEditor.getIn(['template', 'extra_templates', name], null);
    if (template !== null) {
      resolve(template);
    } else {
      this.props.dispatch(actions.loadTemplate(name)).then((code) => {
        this.props.dispatch(actions.setExtraTemplate({ name, code }));
        resolve(code);
      });
    }
  });

  render() {
    return (
      <PortalEditor
        portalEditor={this.props.portalEditor}
        name={this.props.params.name}
        loadTemplate={this.loadTemplate}
        setCurrentWidget={this.setCurrentWidget}
        getPhraseTranslations={this.getPhraseTranslations}
        ref={(c) => { this.editor = c; }}
      />
    );
  }
}

class PortalEditor extends React.Component {
  static propTypes = {
    name:                  PropTypes.string,
    portalEditor:          PropTypes.object,
    loadTemplate:          PropTypes.func,
    setCurrentWidget:      PropTypes.func,
    getPhraseTranslations: PropTypes.func,
    deleteTemplate:        PropTypes.func,
    saveTemplate:          PropTypes.func,
    resetTemplate:         PropTypes.func,
    saveSubmit:            PropTypes.bool,
    undoSubmit:            PropTypes.bool,
    resetSubmit:           PropTypes.bool,
  };

  constructor(props) {
    super(props);

    let name = 'Select a template';
    if (props.name) {
      name = props.name.split(':')[2].replace(/\.twig/, '');
    }

    this.state = {
      currentTemplate:  name,
      templateCode:     '',
      textareaDisabled: true
    };
  }

  componentDidMount() {
    this.compileProps(this.props.portalEditor);
  }

  componentWillReceiveProps(nextProps) {
    this.compileProps(nextProps.portalEditor);
  }

  closeTemplateMenu = () => {
    if (this.templateMenu) {
      this.templateMenu.closeMenu();
    }
  };

  compileProps = (portalEditor) => {
    this.setState({
      templateType:     'block',
      templateCode:     portalEditor.getIn(['template', 'template_code', 'code'], ''),
      textareaDisabled: !portalEditor.get('template')
    });
  };

  render() {
    const {
      contentChanged,
      currentTemplate,
      templateCode,
      textareaDisabled
    } = this.state;

    return (
      <div className="dp-portal-editor">
        <div className="editor">
          <div className="header">
            <div>
              <div className="top-menu">
                <DropDownMenu
                  icon="mail"
                  label={this.props.portalEditor.getIn(['currentTemplate', 'name'], currentTemplate)}
                  className="emails-block-button"
                  ref={(c) => { this.templateMenu = c; }}
                >
                  <TemplatesMenuContainer
                    closeMenu={this.closeTemplateMenu}
                  />
                </DropDownMenu>
              </div>
            </div>
          </div>
          <Editor
            body={templateCode}
            disabled={textareaDisabled}
            ref={(c) => { this.editor = c; }}
            phrases={this.props.portalEditor.get('phrases')}
            getPhraseTranslations={this.props.getPhraseTranslations}
            loadTemplate={this.props.loadTemplate}
            setCurrentWidget={this.props.setCurrentWidget}
          />
          <div className="footer">
            <Button
              size="medium"
              disabled={textareaDisabled || !contentChanged}
              loading={this.props.saveSubmit}
              onClick={this.props.saveTemplate}
            >
              Save changes
            </Button>
            <ConfirmButton
              type="secondary"
              size="medium"
              loading={this.props.undoSubmit}
              disabled={textareaDisabled || !contentChanged}
              onClick={this.undoChanges}
            >
              Undo changes
            </ConfirmButton>
            { this.props.portalEditor.getIn(['currentTemplate', 'is_custom'], false) ?
              <ConfirmButton
                type="secondary"
                size="medium"
                className={classNames('right floated negative')}
                loading={this.props.resetSubmit}
                disabled={textareaDisabled}
                onClick={this.props.deleteTemplate}
              >
                <Icon name={faExclamationTriangle} />
                Delete
              </ConfirmButton>
              :
              <ConfirmButton
                type="secondary"
                size="medium"
                className={classNames('right floated')}
                loading={this.props.resetSubmit}
                disabled={textareaDisabled}
                onClick={this.props.resetTemplate}
              >
                Reset template
              </ConfirmButton>
            }
          </div>
        </div>
      </div>
    );
  }
}
export default PortalEditorContainer;
