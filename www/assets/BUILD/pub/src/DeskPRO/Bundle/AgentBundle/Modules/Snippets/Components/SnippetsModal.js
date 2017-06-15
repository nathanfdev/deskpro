import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import Modal from 'deskpro-styles/lib/Components/Modal';
import Button from 'deskpro-styles/lib/Components/Button';
import Input from 'deskpro-styles/lib/Components/Input';
import InputLabel from 'deskpro-styles/lib/Components/InputLabel';
import LabelInput from 'deskpro-styles/lib/Components/LabelInput';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import * as actions from '../Actions/snippetsActions';
import { allSnippetBlobsSelector } from '../Selectors/snippets';

@connect()
export class SnippetsModalContainer extends React.Component {
  static propTypes = {
    snippet:    PropTypes.object,
    langId:     PropTypes.number,
    closeModal: PropTypes.func,
    dispatch:   PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      labels:      [],
      translation: Immutable.fromJS({}),
    };
  }

  componentWillMount() {
    this.getContent(this.props);
    this.setState({
      labels: this.props.snippet.get('labels', []).toArray()
    });
  }

  componentDidMount() {
    if (typeof DeskPRO_Window !== 'undefined') { // eslint-disable-line camelcase
      DeskPRO_Window.initRteAgentReply(this.modal.textArea, { // eslint-disable-line no-undef
        defaultIsHtml: true,
        autoresize:    false,
        focus:         true
      });
    }
  }

  componentWillReceiveProps(nextProps) {
    this.getContent(nextProps);
    this.setState({
      labels: nextProps.snippet.get('labels', []).toArray()
    });
  }

  getContent = (props) => {
    const { snippet, langId } = props;
    if (!snippet.has('translations')) {
      this.setState({
        translation: Immutable.fromJS({})
      });
      return;
    }
    let translation = snippet.get('translations').find(element => element.get('language') === langId);
    if (!translation) {
      translation = Immutable.fromJS({});
    }
    this.setState({
      translation
    });
  };

  addAttachment = (event, data) => {
    const blob = data.result && data.result.data ? data.result.data : {};
    // Push new blob to collection
    this.props.dispatch(actions.addSnippetAttachment(blob));

    // Add blob to translation Map
    const translation = this.state.translation.toObject();
    translation.blobs = translation.blobs.push(blob.blob_id);
    this.setState({
      translation: Immutable.fromJS(translation)
    });
  };

  saveSnippet = () => {
    const { snippet, langId, dispatch, closeModal } = this.props;
    const snippetData = {
      id:            snippet.get('id', 0),
      title:         this.modal.title.input.value,
      types:         snippet.get('types', ['ticket']),
      shortcut_code: this.modal.shortcut_code.input.value,
      labels:        this.state.labels.map(label => ({ label })),
      translations:  [
        {
          language: langId,
          content:  this.modal.textArea.value,
          blobs:    this.state.translation.get('blobs').toArray()
        }
      ],
      is_draft: '0'
    };
    dispatch(actions.saveSnippet(snippetData))
      .then(() => {
        closeModal();
      }, (error) => {
        console.log(error);
      })
    ;
  };

  changeLabels = (labels) => {
    this.setState({
      labels
    });
  };

  render() {
    const { snippet, closeModal } = this.props;

    return (
      <SnippetsModal
        snippet={snippet}
        labels={this.state.labels}
        translation={this.state.translation}
        closeModal={closeModal}
        addAttachment={this.addAttachment}
        saveSnippet={this.saveSnippet}
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
    labels:        PropTypes.array,
    addAttachment: PropTypes.func,
    saveSnippet:   PropTypes.func,
    closeModal:    PropTypes.func,
    changeLabels:  PropTypes.func,
  };
  static defaultProps = {
    changeLabels() {}
  };

  getUploadUrl = () => '/api/v2/snippets/attachment';

  render() {
    const { snippet, labels, translation } = this.props;
    if (!snippet) {
      return null;
    }
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
          <InputLabel htmlFor="snippet_title">Title</InputLabel>
          <Input
            id="snippet_title"
            defaultValue={snippet.get('title')}
            ref={(c) => { this.title = c; }}
          /><br />
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
          <InputLabel htmlFor="snippet_shortcut_code">Shortcode</InputLabel>
          <Input
            id="snippet_shortcut_code"
            className="snippet_shortcut_code"
            defaultValue={snippet.get('shortcut_code')}
            prefix="%"
            suffix="%"
            ref={(c) => { this.shortcut_code = c; }}
          />
          <InputLabel htmlFor="snippet_ownership">Ownership</InputLabel>
          <Input id="snippet_ownership" />
          <InputLabel htmlFor="snippet_visibility">Visibility</InputLabel>
          <Input id="snippet_visibility" />
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
