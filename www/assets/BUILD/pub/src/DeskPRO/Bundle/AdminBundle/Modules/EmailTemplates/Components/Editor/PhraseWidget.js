import React, { PropTypes } from 'react';
import { render } from 'react-dom';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Field, Input } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Widget from './Widget';

class PhrasePopup extends React.Component {
  static propTypes = {
    phrase:                 PropTypes.string,
    text:                   PropTypes.string,
    getPhraseTranslations:  PropTypes.func,
    savePhraseTranslations: PropTypes.func,
    variables:              PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      translations: {}
    };
  }

  componentWillMount() {
    this.props.getPhraseTranslations(this.props.phrase).then(
      (result) => {
        this.setState({
          translations: result
        });
      }
    );
  }

  getPopUp = () => {
    const { phrase } = this.props;
    return (
      <div className="variable-popup">
        <strong>Phrase: </strong><span className="phrase">{phrase}</span>
        <div className="ui horizontal divider">Translations</div>
        {this.getTranslations()}
        {this.getVariables()}
        <Button onClick={this.saveChanges}>Submit</Button>
        <Button className="basic" onClick={this.closeMenu}>Cancel</Button>
      </div>
    );
  };

  getTranslations = () => {
    this.translationInputs = [];
    return (
      <div className="translations">
        {window.DP_ENABLED_LANGS.map(language =>
          <Field key={`language_${language.locale}`} field={`phrase_${language.locale}`} errors={null}>
            <label htmlFor={`phrase_${language.locale}`}>{language.title}</label>
            <Input
              id={`phrase_${language.locale}`}
              name={`phrase_${language.locale}`}
              defaultValue={this.state.translations[language.locale]}
              ref={(c) => { this.translationInputs.push(c); }}
            />
          </Field>
        )}
      </div>

    );
  };

  getVariables = () => {
    const { variables } = this.props;
    if (Object.keys(variables).length === 0) {
      return null;
    }
    this.variableInputs = [];
    return (
      <div className="variables">
        <div className="ui horizontal divider">Variables</div>
        {Object.keys(variables).map(key =>
          <Field key={`variable_${key}`} field={`variable_${key}`} errors={null}>
            <label htmlFor={`variable_${key}`}>{key}</label>
            <Input
              id={`variable_${key}`}
              name={`variable_${key}`}
              defaultValue={variables[key]}
              ref={(c) => { this.variableInputs.push(c); }}
            />
          </Field>
        )}
      </div>
    );
  };

  openMenu = () => {
    this.popup.openPopup();
  };

  closeMenu = () => {
    this.popup.closePopup();
  };

  saveChanges = () => {
    for (const field of this.variableInputs) {
      if (field && field.input.input.value) {
        // phrase[field.input.name] = field.input.value;
      }
    }
    this.props.savePhraseTranslations();
  };

  render() {
    return (
      <PopUp
        positionMy="left top-1px"
        positionAt="left bottom"
        zIndex={100}
        content={this.getPopUp()}
        ref={(c) => { this.popup = c; }}
        style={{ display: 'inline-block' }}
        autoOpen={false}
        manual
      >
        <span
          onClick={this.openMenu}
        >
          {this.props.text}
        </span>
      </PopUp>
    );
  }
}
class PhraseWidget extends Widget {
  constructor(cm, pos, code, text, getPhraseTranslations, savePhraseTranslations) {
    super(cm, pos);
    try {
      const element = document.createElement('span');
      element.className = 'twig-phrase';
      this.setMark(element, code);

      const matches = code.match(/{{\s*phrase\('([^)]+)'(,\s*{[^}]+})?\)\s*}}/);
      const phrase = matches[1];
      const variables = {};
      const re = /{{([^}]+)}}/g;
      let match = re.exec(text);
      while (match !== null) {
        variables[match[1]] = '';
        match = re.exec(text);
      }
      if (matches[2]) {
        matches[2]
          .substring(3, matches[2].length - 1)
          .split(',')
          .forEach((variable) => {
            const pieces = variable.split(':');
            variables[pieces[0].trim()] = pieces[1].trim();
          }
        );
      }


      render(
        <PhrasePopup
          phrase={phrase}
          code={code}
          text={text}
          variables={variables}
          getPhraseTranslations={getPhraseTranslations}
          savePhraseTranslations={savePhraseTranslations}
        />,
        element
      );
    } catch (e) {
      console.log(e);
    }
  }
}
export default PhraseWidget;
