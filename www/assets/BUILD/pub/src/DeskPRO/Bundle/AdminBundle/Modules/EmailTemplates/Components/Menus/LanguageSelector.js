import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Select } from 'DeskPRO/Component/Semantic/Form';
import * as actions from '../../Actions/templatesActions';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
class LanguageSelector extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired,
    languages:      PropTypes.array
  };

  selectLanguage = (lang) => {
    this.props.dispatch(actions.setCurrentLanguage(lang));
    const group = this.props.emailTemplates.get('currentTemplateGroup');
    this.props.dispatch(actions.loadPhrases(group, lang));
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
            value={this.props.emailTemplates.get('currentLanguage')}
          />
        </div>
      </div>
    );
  }
}
export default LanguageSelector;
