import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import Modal from 'deskpro-styles/lib/Components/Modal';
import Button from 'deskpro-styles/lib/Components/Button';
import Input from 'deskpro-styles/lib/Components/Input';
import Select from 'deskpro-styles/lib/Components/Select';
import InputLabel from 'deskpro-styles/lib/Components/InputLabel';
import LabelInput from 'deskpro-styles/lib/Components/LabelInput';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
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

@connect(state => ({
  languages: allSelectorFactory('Language')(state)
}))
export class SnippetsModalContainer extends React.Component {
  static propTypes = {
    snippet:    PropTypes.object,
    languages:  PropTypes.object,
    langId:     PropTypes.number,
    closeModal: PropTypes.func,
    dispatch:   PropTypes.func,
    type:       PropTypes.string,
  };
  static defaultProps = {
    type: 'ticket'
  };

  constructor(props) {
    super(props);
    this.state = {
      labels:       [],
      translations: [],
      langId:       props.langId,
    };
  }

  componentWillMount() {
    this.setState({
      translations: this.props.snippet.get('translations', []),
      labels:       this.props.snippet.get('labels', new Immutable.List()).toArray()
    });
  }

  componentDidMount() {
    if (typeof DeskPRO_Window !== 'undefined') { // eslint-disable-line camelcase
      const self = this;
      DeskPRO_Window.initRteAgentReply(this.modal.textArea, { // eslint-disable-line no-undef
        defaultIsHtml: true,
        autoresize:    false,
        focus:         true,
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
        translation.set('content', this.redactor.getCode()));
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
    const snippetData = {
      title:         this.modal.title.input.value,
      types:         snippet.get('types', [type]),
      shortcut_code: this.modal.shortcut_code.input.value,
      labels:        this.state.labels.map(label => ({ label })),
      translations:  translations.toJS(),
      is_draft:      '0'
    };
    if (snippet.get('id', false)) {
      snippetData.id = snippet.get('id');
    }
    dispatch(actions.saveSnippet(snippetData))
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

  changeLabels = (labels) => {
    this.setState({
      labels
    });
  };

  render() {
    const { snippet, closeModal, languages } = this.props;

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
        langId={this.state.langId}
        addAttachment={this.addAttachment}
        saveSnippet={this.saveSnippet}
        setLanguage={this.setLanguage}
        changeLabels={this.changeLabels}
        ref={(c) => { this.modal = c; }}
      />
    );
  }
}
export class SnippetsModal extends React.Component {
  static propTypes = {
    snippet:       PropTypes.object,
    translation:   PropTypes.object,
    languages:     PropTypes.object,
    langId:        PropTypes.number,
    labels:        PropTypes.array,
    addAttachment: PropTypes.func,
    saveSnippet:   PropTypes.func,
    setLanguage:   PropTypes.func,
    closeModal:    PropTypes.func,
    changeLabels:  PropTypes.func,
  };
  static defaultProps = {
    changeLabels() {}
  };

  getUploadUrl = () => '/api/v2/snippets/attachment';

  render() {
    const { snippet, labels, translation, languages, langId, setLanguage } = this.props;
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
          title={snippet.get('id', false) ? 'Edit snippet' : 'New snippet'}
          closeModal={this.props.closeModal}
          buttons={
            <div>
              <Button className="dp-button--l" onClick={this.props.saveSnippet}>Save</Button>
              <Button className="dp-button--l dp-button--secondary" onClick={this.props.closeModal}>Cancel</Button>
            </div>
        }
        >
          <form id="snippet_form">

            <div className="title-field field">
              <InputLabel htmlFor="snippet_title" required>Title</InputLabel>
              <Input
                id="snippet_title"
                defaultValue={snippet.get('title')}
                ref={(c) => { this.title = c; }}
                required
              />
            </div>
            <div className="language-field field">
              <Select
                id="snippet_modal_language"
                onChange={setLanguage}
                optionComponent={LanguageOption}
                options={languageOptions.toArray()}
                value={langId}
                clearable={false}
                valueComponent={LanguageValue}
              />
            </div>
            <textarea
              name="editor"
              id="snippet__editor"
              cols="30"
              rows="10"
              defaultValue={translation.get('content', '')}
              ref={(c) => { this.textArea = c; }}
            /><br />
            {translation.get('blobs').map((blobId, key) => <SnippetAttachment key={key} blobId={blobId} />)}<br />
            <UploadButton
              id={'upload_attachment'}
              ref={(c) => { this.uploadButton = c; }}
              name="file"
              onSuccess={this.props.addAttachment}
              uploadUrl={this.getUploadUrl()}
            />
            <InputLabel htmlFor="snippet_label_input">Labels</InputLabel>
            <LabelInput
              labels={labels}
              onChange={this.props.changeLabels}
              inputProps={{
                placeholder: 'Add a label',
                id:          'snippet_label_input'
              }}
            />
            <InputLabel htmlFor="snippet_shortcut_code" required>Shortcode</InputLabel>
            <Input
              id="snippet_shortcut_code"
              className="snippet_shortcut_code"
              defaultValue={snippet.get('shortcut_code')}
              prefix="%"
              suffix="%"
              required
              ref={(c) => { this.shortcut_code = c; }}
            />
            <InputLabel htmlFor="snippet_ownership">Ownership</InputLabel>
            <Input id="snippet_ownership" />
            <InputLabel htmlFor="snippet_visibility">Visibility</InputLabel>
            <Input id="snippet_visibility" />
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
