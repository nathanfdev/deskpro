import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { DragDropContext } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import { faGlobe } from '@fortawesome/free-solid-svg-icons';
import { Icon, List, ListElement, CustomSelect, Checkbox } from '@deskpro/react-components';
import { sortByAttribute } from 'DeskPRO/Component/Util/Map';
import LanguageItem from './LanguageItem';

@DragDropContext(HTML5Backend)
class SortableList extends React.Component {
  static propTypes = {
    langPref: PropTypes.object,
    onChange: PropTypes.func,
    children: PropTypes.node,
  };

  constructor(props) {
    super(props);
    this.moveCard = this.moveCard.bind(this);
  }

  moveCard(dragIndex, hoverIndex) {
    const prefs = [...this.props.langPref];
    const dragCard = prefs[dragIndex];

    prefs.splice(dragIndex, 1);
    prefs.splice(hoverIndex, 0, dragCard);
    this.props.onChange(new Set(prefs));
  }

  render() {
    const childrenWithProps = React.Children.map(this.props.children,
      (child, i) => React.cloneElement(child, {
        moveCard: this.moveCard,
        index:    i
      })
    );

    return (<div>{childrenWithProps}</div>);
  }
}

export class LanguageList extends React.Component {
  static propTypes = {
    languages:   PropTypes.object,
    langContext: PropTypes.array,
    langPref:    PropTypes.object,
    onChange:    PropTypes.func,
    type:        PropTypes.string,
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
      let title;
      switch (this.props.type) {
        case 'ticket':
          title = <FormattedMessage id="agent.general.ticket" />;
          break;
        case 'chat':
          title = <FormattedMessage id="agent.general.chat" />;
          break;
        default:
          title = 'Context';
      }
      return (
        <LanguageItem
          id="context"
          key={`context_lang_${contextLanguage.get('id')}`}
        >
          <Checkbox checked={checked} value="context" readOnly>
            {title} ({contextLanguage.get('title')})
          </Checkbox>
        </LanguageItem>
      );
    }
    return null;
  };

  getAgentLanguage = (checked) => {
    const agentLanguage = this.props.languages.find(lang => lang.get('id') === this.props.langContext[1]);
    if (agentLanguage) {
      return (
        <LanguageItem
          id="agent"
          key={`agent_lang_${agentLanguage.get('id')}`}
        >
          <Checkbox checked={checked} value="agent" readOnly>
            <FormattedMessage id="agent.snippets.your_language" /> ({agentLanguage.get('title')})
          </Checkbox>
        </LanguageItem>
      );
    }
    return null;
  };

  getHelpdeskLanguage = (checked) => {
    const helpdeskLanguage = this.props.languages.find(lang => lang.get('id') === this.props.langContext[2]);
    if (helpdeskLanguage) {
      return (
        <LanguageItem
          id="helpdesk"
          key={`helpdesk_lang_${helpdeskLanguage.get('id')}`}
        >
          <Checkbox checked={checked} value="helpdesk" readOnly>
            <FormattedMessage id="agent.snippets.helpdesk_default" /> ({helpdeskLanguage.get('title')})
          </Checkbox>
        </LanguageItem>
      );
    }
    return null;
  };

  getLanguages = () => {
    const { languages, langPref } = this.props;
    const list = [];
    const draggableList = [];
    const displayedLanguages = [];
    langPref.forEach((langId) => {
      if (displayedLanguages.indexOf(langId) === -1) {
        displayedLanguages.push(langId);
        if (Number.isInteger(langId)) {
          const lang = languages.find(l => l.get('id') === langId);
          draggableList.push(
            <LanguageItem
              id={langId}
              key={langId}
            >
              <Checkbox checked value={langId} onChange={this.updateLanguage}>
                <img src={lang.get('flag_image')} alt={lang.get('title')} />
                &nbsp;{lang.get('title')}
              </Checkbox>
            </LanguageItem>
          );
        } else {
          const functionName = langId.charAt(0).toUpperCase() + langId.slice(1);
          const item = this[`get${functionName}Language`](true);
          if (item) {
            draggableList.push(item);
          }
        }
      }
    });
    list.push(
      <SortableList
        key="sortable"
        onChange={this.props.onChange}
        langPref={this.props.langPref}
      >
        {draggableList}
      </SortableList>
    );
    list.push(
      <ListElement
        key="separator"
        className="separator"
      />
    );
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
    type:        PropTypes.string,
  };

  inputRenderer = () => <span key="label"><Icon name={faGlobe} />&nbsp;<FormattedMessage id="agent.general.languages" /></span>;

  render() {
    const { languages, langContext, langPref, onChange, type } = this.props;
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
          type={type}
        />
      </CustomSelect>
    );
  }
}
