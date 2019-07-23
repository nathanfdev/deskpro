import React from 'react';
import PropTypes from 'prop-types';
import { ArticleEditor } from '@deskpro/product-content-editor';


class ContentEditor extends React.PureComponent {
  static onFocus() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  }

  static onBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  constructor(props) {
    super(props);
    this.editor = React.createRef();
  }

  render() {
    const { value  } = this.props;
    return (
      <ArticleEditor
        ref={this.editor}
        options={{
          initialContent: value
        }}
        onFocus={ContentEditor.onFocus}
        onBlur={ContentEditor.onBlur}
      />
    );
  }
}

ContentEditor.propTypes = {
  value: PropTypes.string,
};

export default ContentEditor;
