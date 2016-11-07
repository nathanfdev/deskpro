import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Select } from 'DeskPRO/Component/Semantic/Form';

class LanguageSelector extends React.Component {
  static propTypes = {
    languages: PropTypes.array
  };

  render() {
    const languages = this.props.languages.map((lang) => {
      const flag = lang.flag.split('.')[0];
      return {
        value: lang.code,
        label: <span><i className={classNames('flag', flag)} />{lang.title}</span>
      };
    });
    return (
      <div className="ui form">
        <div className="ui field">
          <Select
            options={languages}
            className="language-select basic"
          />
        </div>
      </div>
    );
  }
}
export default LanguageSelector;
