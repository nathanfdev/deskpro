import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Select } from 'DeskPRO/Component/Semantic/Form';
import * as actions from '../../Actions/templatesActions';

@connect()
class LanguageSelector extends React.Component {
  static propTypes = {
    dispatch:  PropTypes.func,
    languages: PropTypes.array
  };

  selectLanguage = (lang) => {
    this.props.dispatch(actions.setCurrentLanguage(lang));
    this.props.dispatch(actions.loadPhrases(lang));
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
            onChange={this.selectLanguage}
          />
        </div>
      </div>
    );
  }
}
export default LanguageSelector;
