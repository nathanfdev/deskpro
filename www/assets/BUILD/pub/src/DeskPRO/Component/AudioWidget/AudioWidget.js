import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import classNames from 'classnames';
import TextTab from './TextTab';
import UploadTab from './UploadTab';
import RecordTab from './RecordTab';

class AudioWidgetForm extends React.Component {

  static propTypes = {
    value:           PropTypes.object,
    errors:          PropTypes.object,
    saving:          PropTypes.bool,
    onSubmit:        PropTypes.func,
    hasAutoSpeech:   PropTypes.bool,
    autoSpeechLabel: PropTypes.string
  };

  static defaultProps = {
    hasAutoSpeech: false
  };

  constructor(props) {
    super(props);
    this.state = this.getDefaultState();
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:     this.state.formData.value,
        errorList: nextProps.errors,
        onChange:  this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    event.preventDefault();

    const { formData } = this.state;
    const value = formData.value;
    const blobData = value.blob[value.blob.type];

    if ('download_url' in blobData) {
      delete blobData.download_url;
    }

    this.props.onSubmit({
      name: value.name,
      type: value.blob.type,

      ...blobData
    });
  };

  getDefaultState() {
    const { value, errors, hasAutoSpeech } = this.props;
    const type = value ? value.get('type') : 'text';
    const blobAuth = value ? value.getIn(['blob', 'blob_auth']) : null;
    const downloadUrl = value ? value.getIn(['blob', 'download_url']) : null;
    const isUpload = value && type === 'upload';
    const isRecord = value && type === 'record';

    return {
      formData: createValue({
        value: {
          blob: {
            type,
            text: {
              text:           value ? value.get('text') : '',
              language:       value ? value.get('language') : null,
              auto_generated: value && hasAutoSpeech ? value.get('auto_generated') : false
            },
            upload: {
              blob: {
                blob_auth:    isUpload ? blobAuth : null,
                download_url: isUpload ? downloadUrl : null
              }
            },
            record: {
              name: value ? value.get('name') : '',
              blob: {
                blob_auth:    isRecord ? blobAuth : null,
                download_url: isRecord ? downloadUrl : null
              }
            }
          }
        },
        errorList: errors,
        onChange:  this.onChange
      })
    };
  }

  render() {
    const { saving, hasAutoSpeech, autoSpeechLabel } = this.props;

    return (
      <div>
        <Form onSubmit={this.onSubmit} formValue={this.state.formData}>
          <Fieldset>
            <Field select="blob" label="Choose source">
              <AudioSource
                hasAutoSpeech={hasAutoSpeech}
                autoSpeechLabel={autoSpeechLabel}
              />
            </Field>

            <button className={classNames('ui primary', { loading: saving }, 'button')}>
              Save
            </button>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

class AudioSource extends React.Component {

  static propTypes = {
    value:           PropTypes.object,
    onChange:        PropTypes.func,
    hasAutoSpeech:   PropTypes.bool,
    autoSpeechLabel: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.tabs = {};
  }

  onChangeTab = (type) => {
    const { value, onChange } = this.props;

    if (value.type in this.tabs) {
      const tab = this.tabs[value.type];
      if (tab.stopPlaying) {
        tab.stopPlaying();
      }
    }

    onChange({ ...value, type });
  };

  render() {
    const { value, hasAutoSpeech, autoSpeechLabel } = this.props;
    const type = value.type;
    const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost';

    return (
      <div>
        <div className="ui top attached tabular menu">
          <TabButton
            tabName="text"
            title="Text to audio"
            iconClass="text width"
            onClick={this.onChangeTab}
            active={type === 'text'}
          />
          <TabButton
            tabName="upload"
            title="Upload file"
            iconClass="cloud upload"
            onClick={this.onChangeTab}
            active={type === 'upload'}
          />
          {isSecure &&
            <TabButton
              tabName="record"
              title="Record"
              iconClass="unmute"
              onClick={this.onChangeTab}
              active={type === 'record'}
            />}
        </div>
        <Tab active={type === 'text'}>
          <Field select="text">
            <TextTab
              ref={(c) => { this.tabs.text = c; }}
              hasAutoSpeech={hasAutoSpeech}
              autoSpeechLabel={autoSpeechLabel}
            />
          </Field>
        </Tab>
        <Tab active={type === 'upload'}>
          <Field select="upload">
            <UploadTab ref={(c) => { this.tabs.upload = c; }} />
          </Field>
        </Tab>
        {isSecure &&
          <Tab active={type === 'record'}>
            <Field select="record">
              <RecordTab ref={(c) => { this.tabs.record = c; }} />
            </Field>
          </Tab>}
      </div>
    );
  }
}

class TabButton extends React.Component {

  static propTypes = {
    active:    PropTypes.bool,
    tabName:   PropTypes.string,
    title:     PropTypes.string,
    iconClass: PropTypes.string,
    onClick:   PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();

    const { tabName, onClick } = this.props;
    onClick(tabName);
  };

  render() {
    const { active, title, iconClass } = this.props;

    return (
      <a className={classNames('item', { active })} onClick={this.onClick}>
        <i className={classNames(iconClass, 'icon')} /> {title}
      </a>
    );
  }
}

class Tab extends React.Component {

  static propTypes = {
    active:   PropTypes.bool,
    children: PropTypes.node
  };

  render() {
    const { active, children } = this.props;

    return (
      <div className={classNames('ui bottom attached tab segment', { active })}>
        {children}
      </div>
    );
  }
}


class AddButton extends React.Component {

  static propTypes = {
    onOpen: PropTypes.func
  };

  render() {
    const { onOpen } = this.props;

    return (
      <button onClick={onOpen}>
        Add
      </button>
    );
  }
}

class EditButton extends React.Component {

  static propTypes = {
    value:  PropTypes.object,
    onOpen: PropTypes.func
  };

  render() {
    const { value, onOpen } = this.props;

    return (
      <div>
        <a onClick={(event) => { event.preventDefault(); onOpen(); }}>
          {value.get('name')}
          <i className="write icon" />
        </a>
      </div>
    );
  }
}

class AudioWidget extends React.Component {

  static propTypes = {
    value:               PropTypes.object,
    addButtonComponent:  PropTypes.func,
    editButtonComponent: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened: false
    };
  }

  onOpen = () => {
    this.setState({
      formOpened: true
    });
  };

  onClose = () => {
    this.setState({
      formOpened: false
    });
  };

  render() {
    const { value } = this.props;
    const { addButtonComponent = AddButton, editButtonComponent = EditButton } = this.props;

    return (
      <div>
        <Modal
          isOpen={this.state.formOpened}
          onClose={this.onClose}
          title={value ? 'Edit audio' : 'Add new audio'}
          className="audio-widget"
        >
          <AudioWidgetForm {...this.props} />
        </Modal>

        {value
          ? React.createElement(editButtonComponent, { onOpen: this.onOpen, value })
          : React.createElement(addButtonComponent, { onOpen: this.onOpen })
        }
      </div>
    );
  }
}

export default AudioWidget;
