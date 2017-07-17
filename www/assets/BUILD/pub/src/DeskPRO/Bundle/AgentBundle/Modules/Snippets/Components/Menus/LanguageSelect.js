import PropTypes from 'prop-types';
import React from 'react';
import { Select } from 'deskpro-components/lib/Components/Forms';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

class LanguageOption extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string,
    isFocused: PropTypes.bool,
    onFocus:   PropTypes.func,
    onSelect:  PropTypes.func,
    option:    PropTypes.object.isRequired,
  };

  handleMouseDown = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onSelect(this.props.option, event);
  };

  handleMouseEnter = (event) => {
    this.props.onFocus(this.props.option, event);
  };

  handleMouseMove = (event) => {
    if (this.props.isFocused) return;
    this.props.onFocus(this.props.option, event);
  };

  render() {
    return (
      <div
        className={this.props.className}
        onMouseDown={this.handleMouseDown}
        onMouseEnter={this.handleMouseEnter}
        onMouseMove={this.handleMouseMove}
        title={this.props.option.title}
      >
        {this.props.option.flag_image ? <img src={this.props.option.flag_image} role="presentation" /> : null }
        {this.props.children}
      </div>
    );
  }
}
export class LanguageSelect extends React.PureComponent {
  static propTypes = {
    languages:        PropTypes.object,
    langPref:         PropTypes.array,
    selectedLanguage: PropTypes.func,
    onChange:         PropTypes.func,
  };


  getOptions() {
    const { languages, langPref } = this.props;
    const languageOptions = [];
    console.log(langPref);
    const contextLanguage = languages.find(lang => lang.get('id') === langPref[0]);
    languageOptions.push(
      {
        value: contextLanguage.get('id'),
        label: `Context: ${contextLanguage.get('title')}`
      }
    );
    const agentLanguage = languages.find(lang => lang.get('id') === langPref[1]);
    languageOptions.push(
      {
        value: agentLanguage.get('id'),
        label: `Agent: ${agentLanguage.get('title')}`
      }
    );
    const helpdeskLanguage = languages.find(lang => lang.get('id') === langPref[2]);
    languageOptions.push(
      {
        value: helpdeskLanguage.get('id'),
        label: `HelpDesk: ${helpdeskLanguage.get('title')}`
      }
    );
    languageOptions.push(
      {
        value:    0,
        label:    '-----------------',
        disabled: true,
      }
    );
    languages
      .sort((a, b) => {
        const titleA = a.get('title').toLowerCase();
        const titleB = b.get('title').toLowerCase();
        if (titleA > titleB) {
          return 1;
        } else if (titleA < titleB) {
          return -1;
        }
        return 0;
      })
      .forEach((lang) => {
        if (langPref.indexOf(lang.get('id')) === -1) {
          languageOptions.push(
            {
              value:     lang.get('id'),
              label:     lang.get('title'),
              flagImage: lang.get('flag_image')
            }
          );
        }
      });
    console.log(languageOptions);
    return languageOptions;
  }

  render() {
    return (
      <Select
        icon="globe"
        placeholder={agentPhrases.get('agent.general.language')}
        searchable={false}
        clearable={false}
        simpleValue
        value={this.props.selectedLanguage}
        onChange={this.props.onChange}
        className="language"
        valueComponent={LanguageOption}
        options={this.getOptions()}
      />
    );
  }
}
