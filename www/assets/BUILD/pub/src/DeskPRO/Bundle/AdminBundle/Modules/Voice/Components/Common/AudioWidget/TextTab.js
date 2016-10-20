import React from 'react';
import classNames from 'classnames';
import { Field, Textarea, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseAudioWidgetTab from './BaseAudioWidgetTab';

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

class TextTab extends BaseAudioWidgetTab {

  constructor(props) {
    super(props);
    this.state = {
      text:    null,
      playing: false
    };
  }

  onTogglePreview = (event) => {
    event.preventDefault();

    if (!window.speechSynthesis) {
      return;
    }

    const { value } = this.props;
    const voice = this.getLanguageVoice();

    if (voice) {
      const message = new SpeechSynthesisUtterance(value.text);
      message.voice = voice;

      window.speechSynthesis.speak(message);
    }
  };

  getLanguageVoice = () => {
    const { value } = this.props;
    const previewVoices = {};

    if (!window.speechSynthesis) {
      return null;
    }

    window.speechSynthesis.getVoices().forEach((voice) => {
      previewVoices[voice.lang] = voice;
    });

    return value.language ? previewVoices[value.language] : null;
  };

  render() {
    const { value } = this.props;
    const { playing } = this.state;

    return (
      <div className="text-tab">
        <Field select="text" label="Type the text you’d like to be read out.">
          <Textarea />
        </Field>
        <div className="language-wrapper">
          <Field select="language" className="language-field">
            <Select choices={languageChoices} clearable={false} />
          </Field>
          {this.getLanguageVoice() &&
            <button
              className={classNames('ui basic button preview-button', {
                disabled: !value.text || !value.language
              })}
              onClick={this.onTogglePreview}
            >
              <i className={classNames(playing ? 'stop' : 'play', 'icon')} />
              Preview
            </button>}
        </div>
        <audio ref={(c) => { this.audio = c; }} />
      </div>
    );
  }
}

export default TextTab;
