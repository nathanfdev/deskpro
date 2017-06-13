import React, { PropTypes } from 'react';
import Modal from 'deskpro-styles/lib/Components/Modal';
import Button from 'deskpro-styles/lib/Components/Button';
import Input from 'deskpro-styles/lib/Components/Input';
import InputLabel from 'deskpro-styles/lib/Components/InputLabel';

export class SnippetsModalContainer extends React.Component {
  componentDidMount() {
    DeskPRO_Window.initRteAgentReply(this.modal.textArea, { // eslint-disable-line no-undef
      defaultIsHtml: true,
      autoresize:    false,
      focus:         true
    });
  }

  render() {
    return (
      <SnippetsModal ref={(c) => { this.modal = c; }} />
    );
  }
}
export class SnippetsModal extends React.Component {
  static propTypes = {
    snippet: PropTypes.object
  };

  render() {
    const { snippet } = this.props;
    return (
      <div id="snippets__modal">
        <Modal
          title="Edit snippet"

          buttons={
            <div>
              <Button className="dp-button--l">Save</Button>
              <Button className="dp-button--l dp-button--secondary">Cancel</Button>
            </div>
        }
        >
          <InputLabel htmlFor="snippet_title">Title</InputLabel>
          <Input id="snippet_title" value={snippet.get('title')} /><br />
          <textarea name="editor" id="snippet__editor" cols="30" rows="10" ref={(c) => { this.textArea = c; }} /><br />
          <InputLabel htmlFor="snippet_shortcut_code">Shortcode</InputLabel>
          <Input id="snippet_shortcut_code" value={snippet.get('shortcut_code')} />
          <InputLabel htmlFor="snippet_ownership">Ownership</InputLabel>
          <Input id="snippet_ownership" />
          <InputLabel htmlFor="snippet_visibility">Visibility</InputLabel>
          <Input id="snippet_visibility" />
        </Modal>
      </div>
    );
  }
}
