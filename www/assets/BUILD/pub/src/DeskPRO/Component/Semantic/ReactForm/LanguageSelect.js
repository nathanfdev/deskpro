import React from 'react';
import languages from 'languages/languages.json';
import Select from './Select';

class LanguageSelect extends React.Component {

  render() {
    const choices = Object.keys(languages.lang).map(langCode => ({
      value: langCode,
      label: languages.lang[langCode][0]
    }));

    return <Select {...this.props} clearable={false} choices={choices} />;
  }
}

export default LanguageSelect;
