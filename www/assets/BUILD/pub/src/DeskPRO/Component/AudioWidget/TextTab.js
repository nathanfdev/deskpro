import PropTypes from 'prop-types';
import React from 'react';
import { Field, Textarea, Select, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';

const languageChoices = [
  { value: 'da-DK', label: 'Danish, Denmark' },
  { value: 'de-DE', label: 'German, Germany' },
  { value: 'en-AU', label: 'English, Australia' },
  { value: 'en-CA', label: 'English, Canada' },
  { value: 'en-GB', label: 'English, UK' },
  { value: 'en-IN', label: 'English, India' },
  { value: 'en-US', label: 'English, United States' },
  { value: 'ca-ES', label: 'Catalan, Spain' },
  { value: 'es-ES', label: 'Spanish, Spain' },
  { value: 'es-MX', label: 'Spanish, Mexico' },
  { value: 'fi-FI', label: 'Finnish, Finland' },
  { value: 'fr-CA', label: 'French, Canada' },
  { value: 'fr-FR', label: 'French, France' },
  { value: 'it-IT', label: 'Italian, Italy' },
  { value: 'ja-JP', label: 'Japanese, Japan' },
  { value: 'ko-KR', label: 'Korean, Korea' },
  { value: 'nb-NO', label: 'Norwegian, Norway' },
  { value: 'nl-NL', label: 'Dutch, Netherlands' },
  { value: 'pl-PL', label: 'Polish-Poland' },
  { value: 'pt-BR', label: 'Portuguese, Brazil' },
  { value: 'pt-PT', label: 'Portuguese, Portugal' },
  { value: 'ru-RU', label: 'Russian, Russia' },
  { value: 'sv-SE', label: 'Swedish, Sweden' },
  { value: 'zh-CN', label: 'Chinese (Mandarin)' },
  { value: 'zh-HK', label: 'Chinese (Cantonese)' },
  { value: 'zh-TW', label: 'Chinese (Taiwanese Mandarin)' }
];

class TextTab extends React.Component {

  static propTypes = {
    value:           PropTypes.object,
    hasAutoSpeech:   PropTypes.bool,
    autoSpeechLabel: PropTypes.string
  };

  render() {
    const { value, hasAutoSpeech, autoSpeechLabel } = this.props;

    return (
      <div className="text-tab">
        <Field select="text" label="Type the text you’d like to be read out.">
          <Textarea disabled={hasAutoSpeech && value.auto_generated} />
        </Field>
        {hasAutoSpeech &&
        <Field select="auto_generated">
          <Checkbox label={autoSpeechLabel || 'Auto generate speech'} />
        </Field>}
        <div className="language-wrapper">
          <Field select="language" className="language-field">
            <Select choices={languageChoices} clearable={false} />
          </Field>
        </div>
      </div>
    );
  }
}

export default TextTab;
