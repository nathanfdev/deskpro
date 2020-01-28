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
      <button className="dropdown-item" onClick={this.onClick}>
        <img
          className="dp-po-icon"
          src={portalUrlGenerator.getFlagPath(this.props.getLangFlag(this.props.langCode))}
          alt={this.props.getLangTitle(this.props.langCode)}
        />
        <span className="text">
          {this.props.getLangTitle(this.props.langCode)}
        </span>
      </button>
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
      <div className="dp-po-language" onMouseOver={this.updateTopPos}>
        <button className="dp-po-language-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img src={portalUrlGenerator.getFlagPath(this.getLangFlag(activeLangCode))} alt="" />
          <span className="dp-po-language-link-text">
            {this.getLangTitle(activeLangCode)} <i className="dp-po-icon far fa-angle-down" />
          </span>
        </button>

        <div className="dropdown-menu dropdown-menu-left">
          {this.renderDropdown()}
        </div>
      </div>
    );
  }
}
