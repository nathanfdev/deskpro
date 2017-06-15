import React, { PropTypes } from 'react';
import Modal from 'deskpro-styles/lib/Components/Modal';
import Button from 'deskpro-styles/lib/Components/Button';
import Input from 'deskpro-styles/lib/Components/Input';
import InputLabel from 'deskpro-styles/lib/Components/InputLabel';

export class SnippetsModalContainer extends React.Component {
  static propTypes = {
    snippet:    PropTypes.object,
    langId:     PropTypes.number,
    closeModal: PropTypes.func,
  };

  componentDidMount() {
    if (typeof DeskPRO_Window !== 'undefined') { // eslint-disable-line camelcase
      DeskPRO_Window.initRteAgentReply(this.modal.textArea, { // eslint-disable-line no-undef
        defaultIsHtml: true,
        autoresize:    false,
        focus:         true
      });
    }
  }

  saveSnippet = () => {
    const { snippet, langId } = this.props;
    const snippetData = {
      id:            snippet.get('id', 0),
      title:         this.modal.title.input.value,
      shortcut_code: this.modal.shortcut_code.input.value,
      labels:        [],
      translations:  [
        {
          language: langId,
          content:  this.modal.textArea.value,
          title:    this.modal.title.input.value,
          blobs:    []
        }
      ]
    };
    const snippetModel = {
      title:  'Test title',
      types:  ['ticket'],
      labels: [
        {
          label: 'test_label'
        }
      ],
      translations:
      [
        {
          language: 1,
          content:  'test content'
        }
      ],
      shortcut_code: 'shortcut',
      is_draft:      '1'
    };
    console.log(snippetModel);
    console.log(snippetData);
  };

  render() {
    const { snippet, langId, closeModal } = this.props;
    return (
      <SnippetsModal
        snippet={snippet}
        langId={langId}
        closeModal={closeModal}
        saveSnippet={this.saveSnippet}
        ref={(c) => { this.modal = c; }}
      />
    );
  }
}
export class SnippetsModal extends React.Component {
  static propTypes = {
    snippet:     PropTypes.object,
    langId:      PropTypes.number,
    saveSnippet: PropTypes.func,
    closeModal:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      content: ''
    };
  }

  componentWillMount = () => {
    this.getContent(this.props);
  };

  componentWillReceiveProps(nextProps) {
    this.getContent(nextProps);
  }

  getContent = (props) => {
    const { snippet, langId } = props;
    if (!snippet.has('translations')) {
      this.setState({
        content: ''
      });
      return;
    }
    const translation = snippet.get('translations').find(element => element.get('language') === langId);
    let content = '';
    if (translation) {
      content = translation.get('content');
    }
    this.setState({
      content
    });
  };

  render() {
    const { snippet } = this.props;
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
            defaultValue={this.state.content}
            ref={(c) => { this.textArea = c; }}
          /><br />
          <InputLabel htmlFor="snippet_shortcut_code">Shortcode</InputLabel>
          <Input
            id="snippet_shortcut_code"
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
