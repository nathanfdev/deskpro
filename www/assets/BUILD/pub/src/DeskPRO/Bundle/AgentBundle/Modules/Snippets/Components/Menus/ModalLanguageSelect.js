import PropTypes from 'prop-types';
import React from 'react';
import htmlToText from 'html-to-text';
import { CustomSelect, List, ListElement } from '@deskpro/react-components';

export class ModalLanguageSelect extends React.PureComponent {
  static propTypes = {
    languages:    PropTypes.object,
    translations: PropTypes.object,
    langId:       PropTypes.number,
    onChange:     PropTypes.func,
  };

  onChange = (langId) => {
    this.props.onChange(langId);
    this.select.toggleOpened();
  };

  getLanguages() {
    const { languages, translations } = this.props;
    const list = [];
    const langIds = [];
    translations.forEach((translation) => {
      const language = languages.find(l => l.get('id') === translation.get('language'));
      langIds.push(translation.get('language'));
      const content = htmlToText.fromString(translation.get('content')).substring(0, 40);
      list.push(
        <ListElement
          key={language.get('id')}
          onClick={() => this.onChange(language.get('id'))}
        >
          <img src={language.get('flag_image')} alt={language.get('title')} />
          &nbsp;{language.get('title')}
          <List>
            <ListElement className="content-preview">
              {content}
            </ListElement>
          </List>
        </ListElement>
      );
    });
    if (list.length) {
      list.push(
        <ListElement
          key="separator"
          className="separator"
        >
          ---------------------------
        </ListElement>
      );
    }
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
        if (langIds.indexOf(lang.get('id')) === -1) {
          langIds.push(lang.get('id'));
          list.push(
            <ListElement
              key={lang.get('id')}
              onClick={() => this.onChange(lang.get('id'))}
            >
              <img src={lang.get('flag_image')} alt={lang.get('title')} />
              &nbsp;{lang.get('title')}
            </ListElement>
          );
        }
      });
    return list;
  }

  inputRenderer = () => {
    const { langId, languages } = this.props;
    const selectedLanguage = languages.find(l => l.get('id') === langId);
    if (selectedLanguage) {
      return (
        <span>
          <img src={selectedLanguage.get('flag_image')} alt={selectedLanguage.get('title')} />
          &nbsp;{selectedLanguage.get('title')}
        </span>
      );
    }
    return null;
  };

  render() {
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
        className="language"
        ref={(c) => { this.select = c; }}
      >
        <List
          className="dp-selectable-list"
        >
          {this.getLanguages()}
        </List>
      </CustomSelect>
    );
  }
}
