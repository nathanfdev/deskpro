import React from "react"
import _ from "lodash"
import { portalUrlGenerator } from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator"

class LanguageChoice extends React.Component {
  onClick(e) {
    e.preventDefault();
    this.props.clickLanguage(this.props.langCode);
  }
  render() {
    return (
      <li>
        <a onClick={this.onClick.bind(this)}>
          <img src={portalUrlGenerator.getFlagPath(this.props.getLangFlag(this.props.langCode))}
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

export default class LanguageChanger extends React.Component {
  constructor(props) {
    super(props);
    this.state = {showing_dropdown: false}
  }
  getLangTitle(lang_code) {
    return _.first(_.filter(this.props.enabled_langs, (lang) => lang.code == lang_code)).title;
  }
  getLangFlag(lang_code) {
    return _.first(_.filter(this.props.enabled_langs, (lang) => lang.code == lang_code)).flag;
  }
  clickLanguage(lang_code) {
    this.props.clickLanguage(lang_code);
  }
  render() {
    const active_lang_code = this.props.active_lang_code;
    const enabled_langs = this.props.enabled_langs;

    return (
      <div className="language-changer-widget">
        <a href="#" className="button-small button-language">
          <img src={portalUrlGenerator.getFlagPath(this.getLangFlag(active_lang_code))} alt=""/>
          <span className="text">{this.getLangTitle(active_lang_code)}</span>
          <span className="extra"><i className="fa fa-caret-down"></i></span>
        </a>

        <div className="language-dropdown">
          <ul>
            {this.renderDropdown()}
          </ul>
        </div>
      </div>
    );
  }
  renderDropdown() {
    return _.map(_.filter(this.props.enabled_langs, (lang) => lang.code != this.props.active_lang_code), (lang) => {
        return (
          <LanguageChoice
            key={lang.code}
            clickLanguage={this.clickLanguage.bind(this)}
            getLangTitle={this.getLangTitle.bind(this)}
            getLangFlag={this.getLangFlag.bind(this)}
            langCode={lang.code}
            />
        );
    });
  }
}
