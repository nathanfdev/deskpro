import React from 'react';
import PropTypes from 'prop-types';
import { ArticleEditor } from '@deskpro/product-content-editor';


const ContentEditor = ({ value }) => (
  <ArticleEditor
    options={{
      initialContent: value
    }}
  />
  );

ContentEditor.propTypes = {
  value: PropTypes.string,
};

export default ContentEditor;
