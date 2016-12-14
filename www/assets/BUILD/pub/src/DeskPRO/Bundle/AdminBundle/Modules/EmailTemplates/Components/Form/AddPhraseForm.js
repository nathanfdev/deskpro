import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Form, Field, Input } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import classNames from 'classnames';
import * as actions from '../../Actions/templatesActions';

@connect()
export class AddPhraseFormContainer extends React.Component {
  static propTypes = {
    dispatch:  PropTypes.func,
    languages: PropTypes.array
  };

  constructor(props) {
    super(props);
    this.state = {
      savingNewPhrase: false
    };
  }

  addPhrase = () => {
    this.setState({
      savingNewPhrase: true,
      errors:          null
    });
    const phrase = {
      name: this.form.nameInput.input.value
    };
    for (const field of this.form.translationInputs) {
      if (field.input.value) {
        phrase[field.input.name] = field.input.value;
      }
    }
    this.props.dispatch(actions.saveCustomPhrase(phrase)).then(
      () => {
        this.setState({
          savingNewPhrase: false
        });
      },
      (response) => {
        this.setState({
          savingNewPhrase: false,
          errors:          response.getData().errors
        });
      }
    );
  };

  render() {
    return (<AddPhraseForm
      ref={(c) => { this.form = c; }}
      languages={this.props.languages}
      addPhrase={this.addPhrase}
      {...this.state}
    />);
  }
}

export class AddPhraseForm extends React.Component {
  static propTypes = {
    languages:       PropTypes.array,
    savingNewPhrase: PropTypes.bool,
    errors:          PropTypes.object,
    addPhrase:       PropTypes.func
  };

  render() {
    this.translationInputs = [];
    const translations = this.props.languages.map(
      language => <Field key={`language_${language.locale}`} field={`phrase_${language.locale}`} errors={this.props.errors}>
        <label htmlFor={`phrase_${language.locale}`}>{language.title}</label>
        <Input id={`phrase_${language.locale}`} name={`phrase_${language.locale}`} ref={(c) => { this.translationInputs.push(c); }} />
      </Field>
    );
    return (
      <div className="add-phrase">
        <Form>
          <Field field="name" className="inline" errors={this.props.errors}>
            <label htmlFor="phrase_name">Phrase: custom.</label>
            <Input id="phrase_name" key="name" ref={(c) => { this.nameInput = c; }} />
          </Field>
          {translations}
          <Button
            onClick={this.props.addPhrase}
            disabled={this.props.savingNewPhrase}
            className={classNames({ loading: this.props.savingNewPhrase })}
          >
            Submit
          </Button>
        </Form>
      </div>
    );
  }
}
