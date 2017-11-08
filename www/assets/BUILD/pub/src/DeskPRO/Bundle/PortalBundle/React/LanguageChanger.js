import PropTypes from 'prop-types';
import React from 'react';
import first from 'lodash/first';
import filter from 'lodash/filter';
import map from 'lodash/map';
import $ from 'jquery';
import { portalUrlGenerator } from '../Http/PortalUrlGenerator';

class LanguageChoice extends React.Component {
  static propTypes = {
    langCode:      PropTypes.string,
    clickLanguage: PropTypes.func,
    getLangFlag:   PropTypes.func,
    getLangTitle:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.onClick = this.onClick.bind(this);
  }

  onClick(e) {
    e.preventDefault();
    this.props.clickLanguage(this.props.langCode);
  }
  render() {
    return (
      <li>
        <a onClick={this.onClick}>
          <img
            src={portalUrlGenerator.getFlagPath(this.props.getLangFlag(this.props.langCode))}
            alt={this.props.getLangTitle(this.props.langCode)}
          />
          <span className="text">
            {this.props.getLangTitle(this.props.langCode)}
          </span>
        </a>
      </li>
    );
  }
}

export class LanguageChanger extends React.Component {
  static propTypes = {
    activeLangCode: PropTypes.string,
    enabledLangs:   PropTypes.array,
    clickLanguage:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.clickLanguage = this.clickLanguage.bind(this);
    this.getLangTitle = this.getLangTitle.bind(this);
    this.getLangFlag = this.getLangFlag.bind(this);
  }

  getLangTitle(langCode) {
    return first(filter(this.props.enabledLangs, lang => lang.code === langCode)).title;
  }

  getLangFlag(langCode) {
    return first(filter(this.props.enabledLangs, lang => lang.code === langCode)).flag;
  }

  clickLanguage(langCode) {
    this.props.clickLanguage(langCode);
  }

  updateTopPos = () => {
    if (!this.langDropdown) {
      return;
    }
    const $langDropdown = $(this.langDropdown);
    const $header = $langDropdown.closest('.top-bar');

    if (!$header[0]) {
      return;
    }

    $langDropdown.css({ top: $header.height() + 8 });
  };

  renderDropdown() {
    return map(filter(this.props.enabledLangs, lang => lang.code !== this.props.activeLangCode), lang => (
      <LanguageChoice
        key={lang.code}
        clickLanguage={this.clickLanguage}
        getLangTitle={this.getLangTitle}
        getLangFlag={this.getLangFlag}
        langCode={lang.code}
      />
    ));
  }

  render() {
    const activeLangCode = this.props.activeLangCode;

    return (
      <div className="language-changer-widget" onMouseOver={this.updateTopPos}>
        <a href="#" className="button-small button-language">
          <img src={portalUrlGenerator.getFlagPath(this.getLangFlag(activeLangCode))} alt="" />
          {this.getLangTitle(activeLangCode)} <i className="fa fa-caret-down" />
        </a>

        <div className="language-dropdown" ref={(c) => { this.langDropdown = c; }}>
          <ul>
            {this.renderDropdown()}
          </ul>
        </div>
      </div>
    );
  }
}
