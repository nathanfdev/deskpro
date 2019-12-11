import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { fromJS } from 'immutable';
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';
import { Button, ConfirmButton, Icon } from '@deskpro/react-components';
import Editor from 'DeskPRO/Component/CMEditor/Editor';
import DropDownMenu from 'DeskPRO/Component/CMEditor/Menus/DropDownMenu';
import { TemplatesMenuContainer } from './Menus/TemplatesMenu';
import { AssetsMenuContainer } from './Menus/AssetsMenu';
import * as actions from '../Actions/templatesActions';
import { PhrasesMenuContainer } from '../../EmailTemplates/Components/Menus/PhrasesMenu';
import { replaceRoute } from '../../../Services/history';

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
      saveSubmit:    false,
      undoSubmit:    false,
      resetSubmit:   false,
      currentWidget: null,
      editor:        null,
    };
  }


  componentWillMount() {
    const { dispatch } = this.props;

    dispatch(actions.unselectTemplate());

    dispatch(actions.loadAssets());
    dispatch(actions.loadTemplates());

    if (this.props.params.name) {
      dispatch(actions.loadTemplate(this.props.params.name.replace('|', '/'))).then((template) => {
        dispatch(actions.setTemplate(template));
      });
    }
    dispatch(actions.loadPhrases(
      'user',
      this.props.portalEditor.get('currentLanguage')
    ));
  }

  setEditor = (editor) => {
    this.setState({
      editor
    });
  };

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
  };

  saveTemplate = () => {
    this.setState({
      saveSubmit: true
    });

    let name;
    if (this.props.params) {
      name = this.props.params.name.replace('|', '/');
    } else {
      name = this.props.portalEditor.getIn(['currentTemplate', 'name']);
    }
    console.log(name);

    const template = {
      code: this.props.portalEditor.getIn(['template', 'template_code', 'code'])
    };

    const promises = [];
    promises.push(this.props.dispatch(actions.saveTemplate(name, template)));

    const extraTemplates = this.props.portalEditor.getIn(['template', 'extra_templates'], fromJS({})).toObject();

    Object.keys(extraTemplates).forEach((key) => {
      promises.push(this.props.dispatch(actions.saveTemplate(key, { code: extraTemplates[key] })));
    });
    Promise.all(promises).then(
      () => {
        this.setState({
          saveSubmit: false
        });
        this.props.dispatch(actions.cleanExtraTemplates());
      }
    );
  };

  changeTemplateCode = (value) => {
    this.props.dispatch(actions.updateTemplateCode(value));
  };

  insertInlineImage = (file) => {
    const tag = `<img src="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}" alt="" />`;
    this.state.editor.getCodeMirror().replaceSelection(tag);
  };

  insertAssetAsLink = (e, file) => {
    e.stopPropagation();
    const tag = `<a href="{{ url('serve_blob', {'blob_auth_id': '${file.get('blob_id')}', 'filename': '${file.get('name')}'}) }}">${file.get('name')}</a>`;
    this.state.editor.getCodeMirror().replaceSelection(tag);
  };

  insertPhrase = (phrase) => {
    this.state.editor.getCodeMirror().replaceSelection(phrase);
  };

  loadTemplate = name => new Promise((resolve) => {
    const template = this.props.portalEditor.getIn(['template', 'extra_templates', name], null);
    if (template !== null) {
      resolve(template);
    } else {
      this.props.dispatch(actions.loadTemplate(name)).then((t) => {
        this.props.dispatch(actions.setExtraTemplate({ name, code: t.original_code.code }));
        resolve(t.original_code.code);
      });
    }
  });

  loadTagInfo = name => new Promise((resolve) => {
    const template = this.props.portalEditor.getIn(['template', 'tags', name], null);
    if (template !== null) {
      resolve(template);
    } else {
      this.props.dispatch(actions.loadTagInfo(name)).then((info) => {
        this.props.dispatch(actions.setTag({ name, info }));
        resolve(info);
      });
    }
  });

  close = () => {
    replaceRoute(`/portal/${this.props.params.brandId}/portal_editor`);
  };

  render() {
    const name = this.props.params.name ? this.props.params.name.replace('|', '/') : '';
    return (
      <PortalEditor
        portalEditor={this.props.portalEditor}
        name={name}
        brandId={this.props.params.brandId}
        loadTagInfo={this.loadTagInfo}
        loadTemplate={this.loadTemplate}
        setTemplateValue={this.setTemplateValue}
        setCurrentWidget={this.setCurrentWidget}
        getPhraseTranslations={this.getPhraseTranslations}
        insertAsset={this.insertInlineImage}
        insertAssetAsLink={this.insertAssetAsLink}
        insertPhrase={this.insertPhrase}
        changeTemplateCode={this.changeTemplateCode}
        saveTemplate={this.saveTemplate}
        close={this.close}
        setEditor={this.setEditor}
        saveSubmit={this.state.saveSubmit}
        undoSubmit={this.state.undoSubmit}
        resetSubmit={this.state.resetSubmit}
        ref={(c) => { this.editor = c; }}
      />
    );
  }
}

class PortalEditor extends React.Component {
  static propTypes = {
    name:                  PropTypes.string,
    brandId:               PropTypes.string,
    portalEditor:          PropTypes.object,
    loadTagInfo:           PropTypes.func,
    loadTemplate:          PropTypes.func,
    setTemplateValue:      PropTypes.func,
    changeTemplateCode:    PropTypes.func,
    setCurrentWidget:      PropTypes.func,
    getPhraseTranslations: PropTypes.func,
    deleteTemplate:        PropTypes.func,
    saveTemplate:          PropTypes.func,
    resetTemplate:         PropTypes.func,
    insertAsset:           PropTypes.func,
    insertAssetAsLink:     PropTypes.func,
    insertPhrase:          PropTypes.func,
    close:                 PropTypes.func,
    setEditor:             PropTypes.func,
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

  setTemplateValue = (name, code) => {
    this.props.setTemplateValue(name, code);
    this.checkChanges();
  };

  closeMediaMenu = () => {
    if (this.mediaMenu) {
      this.mediaMenu.closeMenu();
    }
  };

  closePhrasesMenu = () => {
    if (this.phrasesMenu) {
      this.phrasesMenu.closeMenu();
    }
  };

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

  handleChange = (value) => {
    if (this.props.portalEditor.get('template')) {
      this.props.changeTemplateCode(value);
      this.checkChanges(value);
    }
  };

  checkChanges = (value = null) => {
    const code = this.props.portalEditor.getIn(['template', 'original_code', 'code'], '');
    const extraTemplates = this.props.portalEditor.getIn(['template', 'extra_templates'], fromJS({}));

    const newCode = value || this.state.templateCode;
    if (code !== newCode || extraTemplates.size) {
      this.setState({
        contentChanged: true
      });
    } else {
      this.setState({
        contentChanged: false
      });
    }
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
                    brandId={this.props.brandId}
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
                  positionMy="right top-1px"
                  positionAt="right bottom"
                >
                  <AssetsMenuContainer
                    closeMenu={this.closeMediaMenu}
                    insertAsset={this.props.insertAsset}
                    insertAssetAsLink={this.props.insertAssetAsLink}
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
                  positionMy="right top-1px"
                  positionAt="right bottom"
                >
                  <PhrasesMenuContainer
                    closeMenu={this.closePhrasesMenu}
                    languages={window.DP_ENABLED_LANGS}
                    insertPhrase={this.props.insertPhrase}
                    data={this.props.portalEditor}
                  />
                </DropDownMenu>
              </div>
            </div>
          </div>
          <Editor
            body={templateCode}
            disabled={textareaDisabled}
            changeTemplateBody={this.handleChange}
            ref={(c) => { this.editor = c; }}
            phrases={this.props.portalEditor.get('phrases')}
            getPhraseTranslations={this.props.getPhraseTranslations}
            loadTagInfo={this.props.loadTagInfo}
            loadTemplate={this.props.loadTemplate}
            setCurrentWidget={this.props.setCurrentWidget}
            setTemplateValue={this.setTemplateValue}
            setEditor={this.props.setEditor}
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
            <Button
              size="medium"
              className={classNames('right floated')}
              onClick={this.props.close}
            >
              Close
            </Button>
          </div>
        </div>
      </div>
    );
  }
}
export default PortalEditorContainer;
