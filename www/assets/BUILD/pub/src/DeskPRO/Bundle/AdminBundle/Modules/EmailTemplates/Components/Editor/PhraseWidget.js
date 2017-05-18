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
    getText:                PropTypes.func,
    savePhraseTranslations: PropTypes.func,
    setCurrentWidget:       PropTypes.func,
    setText:                PropTypes.func,
    variables:              PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      translations: {},
      variables:    {},
      text:         this.props.getText(),
    };
  }

  componentWillMount() {
    this.setState({
      variables: this.props.variables
    });
  }

  getAdvanced = () => (
    <div className="advanced">
      <div className="ui horizontal divider">Advanced</div>
      <Field field="advanced" errors={null}>
        <label htmlFor="advanced">Raw value</label>
        <Input
          id="advanced"
          name="advanced"
          value={this.state.text}
          onChange={this.handleChangeAdvanced}
        />
      </Field>
    </div>
    );

  getPopUp = () => {
    const { phrase } = this.props;
    return (
      <div className="variable-popup">
        <strong>Phrase: </strong><span className="phrase">{phrase}</span>
        <div className="ui horizontal divider">Translations</div>
        {this.getTranslations()}
        {this.getVariables()}
        {this.getAdvanced()}
        <Button onClick={this.saveChanges}>Submit</Button>
        <Button className="basic" onClick={this.closePopup}>Cancel</Button>
      </div>
    );
  };

  getTranslations = () => (
    <div className="translations">
      {window.DP_ENABLED_LANGS.map(language =>
        <Field key={`language_${language.locale}`} field={`phrase_${language.locale}`} errors={null}>
          <label htmlFor={`phrase_${language.locale}`}>{language.title}</label>
          <Input
            id={`phrase_${language.locale}`}
            name={`phrase_${language.locale}`}
            value={this.state.translations[language.locale]}
            onChange={(value) => { this.handleChangeTranslation(value, language.locale); }}
          />
        </Field>
        )}
    </div>
    );

  getVariables = () => {
    const { variables } = this.state;
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

  handleChangeTranslation = (value, locale) => {
    const translations = this.state.translations;
    translations[locale] = value;
    this.setState({
      translations
    });
  };

  handleChangeAdvanced = (value) => {
    this.setState({
      text: value
    });
  };

  openPopup = () => {
    this.props.getPhraseTranslations(this.props.phrase).then(
      (result) => {
        this.setState({
          translations: result
        });
      }
    );
    this.popup.openPopup();
    this.props.setCurrentWidget(this, null);
  };

  closePopup = () => {
    this.popup.closePopup();
  };

  saveChanges = () => {
    this.props.savePhraseTranslations(this.props.phrase, this.state.translations).then(() => {
      if (this.variableInputs) {
        const variableCode = [];
        const variables = {};
        for (const field of this.variableInputs) {
          if (field) {
            const key = field.input.name.replace(/^variable_/, '');
            if (field.input.value) {
              const value = field.input.value;
              variableCode.push(`${key}: ${value}`);
              variables[key] = value;
            } else {
              variables[key] = '';
            }
          }
        }
        this.setState({
          variables
        });
        let text = this.props.getText();
        if (variableCode.length) {
          text = text.replace(/(\('[^']+')[^)]*(\))/, `$1, { ${variableCode.join(', ')} }$2`);
        } else {
          text = text.replace(/(\('[^']+')[^)]*(\))/, '$1$2');
        }
        this.props.setText(text, 'popup');
      }
      this.popup.closePopup();
    });
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
        clickOut={false}
        manual
      >
        <span
          onClick={this.openPopup}
        >
          {this.props.text}
        </span>
      </PopUp>
    );
  }
}
class PhraseWidget extends Widget {
  constructor(cm, pos, code, text, setCurrentWidget, getPhraseTranslations, savePhraseTranslations) {
    super(cm, pos);
    try {
      const element = document.createElement('span');
      element.className = 'twig-phrase';
      this.setMark(element, code);

      this.code = code;
      this.text = text;
      this.getPhraseTranslations = getPhraseTranslations;
      this.savePhraseTranslations = savePhraseTranslations;
      this.setCurrentWidget = setCurrentWidget;

      this.addReactComponent(element);
    } catch (e) {
      console.error(e);
    }
  }

  addReactComponent = (element) => {
    const matches = this.code.match(/{{\s*phrase\('([^)]+)'(,\s*{[^}]+})?\)\s*}}/);
    const phrase = matches[1];
    const variables = {};
    const re = /{{([^}]+)}}/g;
    let match = re.exec(this.text);
    while (match !== null) {
      variables[match[1]] = '';
      match = re.exec(this.text);
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
        code={this.code}
        text={this.text}
        variables={variables}
        getPhraseTranslations={this.getPhraseTranslations}
        savePhraseTranslations={this.savePhraseTranslations}
        setCurrentWidget={this.setCurrentWidget}
        setText={this.setText}
        getText={this.getText}
      />,
      element
    );
  }
}
export default PhraseWidget;
