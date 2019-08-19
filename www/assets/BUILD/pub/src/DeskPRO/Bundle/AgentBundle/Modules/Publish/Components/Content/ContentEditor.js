import React from 'react';
import PropTypes from 'prop-types';
import { ArticleEditor } from '@deskpro/product-content-editor';
import '@deskpro/content-editor/styles/content.css';


class ContentEditor extends React.PureComponent {

  constructor(props) {
    super(props);
    this.editor = React.createRef();
  }

  onFocus = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    this.props.onFocus();
  };

  onBlur = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
    this.props.onBlur();
  };

  render() {
    const { value  } = this.props;
    return (
      <ArticleEditor
        ref={this.editor}
        options={{
          initialContent: value
        }}
        onFocus={this.onFocus}
        onBlur={this.onBlur}
        uppyOptions={{
          autoProceed: false,
          xhrUpload:   {
            endpoint:             `${window.ASSETS_BASE_URL_FULL.replace('^http(s)?:', '').replace(window.ASSETS_BASE_URL, '')}/agent/misc/accept-redactor-image-upload`,
            fieldName:            'file',
            responseUrlFieldName: 'link',
            method:               'POST',
            meta:                 {
              _rt:  window.DP_REQUEST_TOKEN,
              json: true
            }
          }
        }}
      />
    );
  }
}

ContentEditor.propTypes = {
  value:   PropTypes.PropTypes.object,
  onFocus: PropTypes.PropTypes.func,
  onBlur:  PropTypes.PropTypes.func,
};

ContentEditor.defaultProps = {
  onFocus() {},
  onBlur() {},
};

export default ContentEditor;
