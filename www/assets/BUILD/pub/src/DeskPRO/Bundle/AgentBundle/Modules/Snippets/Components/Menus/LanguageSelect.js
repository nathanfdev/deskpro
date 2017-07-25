import React, { PropTypes } from 'react';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import Icon from 'deskpro-components/lib/Components/Icon';
import { CustomSelect, Checkbox } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import { sortByAttribute } from 'DeskPRO/Component/Util/Map';

class LanguageList extends React.Component {
  static propTypes = {
    languages:   PropTypes.object,
    langContext: PropTypes.array,
    langPref:    PropTypes.object,
    onChange:    PropTypes.func,
  };

  static defaultProps = {
    langPref: new Set(['context', 'agent', 'helpdesk'])
  };

  componentWillMount() {
    this.languageList = ['context', 'agent', 'helpdesk'];
    this.languageList = this.languageList.concat(
      this.props.languages
        .sort((a, b) => sortByAttribute(a, b, 'title'))
        .map(lang => lang.get('id')
    ).toArray());
  }

  getContextLanguage = (checked) => {
    const contextLanguage = this.props.languages.find(lang => lang.get('id') === this.props.langContext[0]);
    if (contextLanguage) {
      return (
        <ListElement
          key={`context_lang_${contextLanguage.get('id')}`}
          className="movable"
        >
          <Checkbox checked={checked} value="context" readOnly>
            Context ({contextLanguage.get('title')})
          </Checkbox>
        </ListElement>
      );
    }
    return null;
  };

  getAgentLanguage = (checked) => {
    const agentLanguage = this.props.languages.find(lang => lang.get('id') === this.props.langContext[1]);
    if (agentLanguage) {
      return (
        <ListElement
          key={`agent_lang_${agentLanguage.get('id')}`}
          className="movable"
        >
          <Checkbox checked={checked} value="agent" readOnly>
            Agent ({agentLanguage.get('title')})
          </Checkbox>
        </ListElement>
      );
    }
    return null;
  };

  getHelpdeskLanguage = (checked) => {
    const helpdeskLanguage = this.props.languages.find(lang => lang.get('id') === this.props.langContext[2]);
    if (helpdeskLanguage) {
      return (
        <ListElement
          key={`helpdesk_lang_${helpdeskLanguage.get('id')}`}
          className="movable"
        >
          <Checkbox checked={checked} value="helpdesk" readOnly>
            HelpDesk ({helpdeskLanguage.get('title')})
          </Checkbox>
        </ListElement>
      );
    }
    return null;
  };

  getLanguages = () => {
    const { languages, langPref } = this.props;
    const list = [];
    const displayedLanguages = [];
    langPref.forEach((langId) => {
      if (displayedLanguages.indexOf(langId) === -1) {
        displayedLanguages.push(langId);
        if (Number.isInteger(langId)) {
          const lang = languages.find(l => l.get('id') === langId);
          list.push(
            <ListElement
              key={lang.get('id')}
              className="movable"
            >
              <Checkbox checked value={lang.get('id')} onChange={this.updateLanguage}>
                <img src={lang.get('flag_image')} alt={lang.get('title')} />
                &nbsp;{lang.get('title')}
              </Checkbox>
            </ListElement>
          );
        } else {
          const functionName = langId.charAt(0).toUpperCase() + langId.slice(1);
          list.push(this[`get${functionName}Language`](true));
        }
      }
    });
    if (list.length) {
      list.push(
        <ListElement
          key="separator"
          className="separator"
        />
      );
    }
    this.languageList
      .forEach((langId) => {
        if (displayedLanguages.indexOf(langId) === -1) {
          displayedLanguages.push(langId);
          if (Number.isInteger(langId)) {
            const lang = languages.find(l => l.get('id') === langId);
            list.push(
              <ListElement
                key={lang.get('id')}
              >
                <Checkbox checked={false} value={lang.get('id')} onChange={this.updateLanguage}>
                  <img src={lang.get('flag_image')} alt={lang.get('title')} />
                  &nbsp;{lang.get('title')}
                </Checkbox>
              </ListElement>
            );
          } else {
            const functionName = langId.charAt(0).toUpperCase() + langId.slice(1);
            list.push(this[`get${functionName}Language`](false));
          }
        }
      });
    return list;
  };

  updateLanguage = (checked, langId) => {
    const langPref = this.props.langPref;
    if (checked) {
      langPref.add(langId);
    } else {
      langPref.delete(langId);
    }
    this.props.onChange(langPref);
    this.forceUpdate();
  };

  render() {
    return (
      <List>
        {this.getLanguages()}
      </List>
    );
  }
}

export class LanguageSelect extends React.PureComponent {
  static propTypes = {
    languages:   PropTypes.object,
    langContext: PropTypes.array,
    langPref:    PropTypes.object,
    onChange:    PropTypes.func,
  };

  inputRenderer = () => <span><Icon name="globe" />&nbsp;{agentPhrases.get('agent.general.languages')}</span>;

  render() {
    const { languages, langContext, langPref, onChange } = this.props;
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
        className="language dp-input--with-icon"
      >
        <LanguageList
          languages={languages}
          langContext={langContext}
          langPref={langPref}
          onChange={onChange}
        />
      </CustomSelect>
    );
  }
}
