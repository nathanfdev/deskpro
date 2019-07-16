import React from 'react';
import PropTypes from 'prop-types';
import { HtmlEditor, MenuBar } from '@aeaton/react-prosemirror';
import { options, menu } from '@aeaton/react-prosemirror-config-default';


const ContentEditor = ({ value, onChange }) => (
  <HtmlEditor
    options={options}
    value={value}
    onChange={onChange}
    render={({ editor, view }) => (
      <div>
        <MenuBar menu={menu} view={view} />
        {editor}
      </div>
      )}
  />
  );

ContentEditor.propTypes = {
  onChange: PropTypes.func,
  value:    PropTypes.string,
};

export default ContentEditor;
