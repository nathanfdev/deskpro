import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage, FormattedRelative } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Select, Modal, Icon } from '@deskpro/react-components';
import AgentAvatar from 'DeskPRO/Component/Avatar/AgentAvatar';
import * as actions from '../Actions/snippetsActions';
import { ComparisonModal } from './ComparisonModal';

@connect()
export class ChangeLogModal extends React.Component {
  static propTypes = {
    snippet:       PropTypes.object,
    languages:     PropTypes.object,
    translation:   PropTypes.object,
    translations:  PropTypes.object,
    closeModal:    PropTypes.func,
    dispatch:      PropTypes.func,
    revertContent: PropTypes.func,
    langId:        PropTypes.number,
    type:          PropTypes.string,
  };

  constructor(props) {
    super(props);
    this.state = {
      loading:             true,
      comparisonModalOpen: false,
      changes:             [],
      version:             0,
      translation:         this.props.translation,
      langId:              this.props.langId,
      type:                this.props.type,
    };
    this.handleChangeClick = this.handleChangeClick.bind(this);
  }

  componentWillMount() {
    this.props.dispatch(actions.getChangelog(this.props.snippet.get('id')))
      .success(({ data }) => {
        this.setState({
          loading: false,
          changes: data,
        });
      });
  }

  getChanges = () => {
    const changes = this.state.changes.filter(change =>
      change.language === this.state.langId && (change.type === null || change.type === this.state.type));
    return changes.map((change, key) => {
      const version = changes.length + 1 - key;
      return (<div key={change.id} className="change" onClick={() => this.handleChangeClick(version)}>
        <Icon name="file-text" size="s" />
        <FormattedMessage id="agent.snippets.content_change" /> (#{version})
            <AgentAvatar agent={change.person} />
        <span className="date"><FormattedRelative value={change.date_created} /></span>
      </div>);
    }
      );
  };

  getComparisonModal = () => {
    if (!this.state.comparisonModalOpen) {
      return null;
    }
    const changes = this.state.changes.filter(change =>
      change.language === this.state.langId && (change.type === null || change.type === this.state.type));
    return (
      <ComparisonModal
        snippet={this.props.snippet}
        changes={changes}
        version={this.state.version}
        translation={this.state.translation}
        revertContent={this.revertContent}
        closeModal={this.closeComparisonModal}
      />
    );
  };

  getLanguages = () => {
    const { languages, snippet } = this.props;
    const options = [];
    const translations = this.props.translations.filter(translation =>
      !snippet.get('is_split', false) || translation.get('type') === this.state.type);
    if (translations.size < 2) {
      return null;
    }
    translations.forEach((translation) => {
      const language = languages.find(l => l.get('id') === translation.get('language'));
      options.push(
        {
          value: language.get('id'),
          label: <span>
            <img src={language.get('flag_image')} alt={language.get('title')} /> {language.get('title')}
          </span>
        }
      );
    });
    return (
      <Select
        onChange={this.selectLanguage}
        options={options}
        searchable={false}
        clearable={false}
        value={this.state.langId}
      />
    );
  };

  getTypes = () => {
    if (!this.props.snippet.get('is_split', false)) {
      return null;
    }
    const options = [
      {
        value: 'ticket',
        label: <FormattedMessage id="agent.general.ticket" />
      },
      {
        value: 'chat',
        label: <FormattedMessage id="agent.general.chat" />
      },
    ];
    return (
      <Select
        onChange={this.selectType}
        options={options}
        searchable={false}
        clearable={false}
        value={this.state.type}
      />
    );
  };

  handleChangeClick(version) {
    this.setState({
      version,
      comparisonModalOpen: true,
    });
  }

  selectLanguage = (language) => {
    const translation = this.props.translations.find(t => t.get('language') === language.value);
    this.setState({
      langId: language.value,
      translation,
    });
  };

  selectType = (type) => {
    const translation = this.props.translations.find(t => t.get('type') === type.value);
    this.setState({
      type: type.value,
      translation,
    });
  };

  revertContent = (content) => {
    const type = this.props.snippet.get('is_split', false) ? this.state.type : null;
    this.props.revertContent(content, this.state.langId, type);
  };

  closeComparisonModal = () => {
    this.setState({
      comparisonModalOpen: false,
    });
  };

  render() {
    const { snippet, closeModal } = this.props;
    const translations = this.props.translations.filter(translation =>
      !snippet.get('is_split', false) || translation.get('type') === this.state.type);
    const changes = this.state.changes.filter(change =>
      change.language === this.state.langId && (change.type === null || change.type === this.state.type));
    return (
      <div id="change_log_modal">
        <Modal
          title={<FormattedMessage id="agent.general.changelog" />}
          closeModal={closeModal}
        >
          {translations.size > 1 || snippet.get('is_split', false) ?
            <div className="display-options">
              <FormattedMessage id="agent.general.display_options" />:
              {this.getLanguages()}
              {this.getTypes()}
            </div>
          : null }
          {this.state.loading ?
            <div className="ui active inverted dimmer">
              <div className="ui text loader">{<FormattedMessage id="agent.general.loading_dot" />}</div>
            </div>
            : <div>
              {this.getChanges()}
              <div
                className={classNames('change', { creation: changes.length === 0 })}
                onClick={() => changes.length !== 0 && this.handleChangeClick(2)}
              >
                <Icon name="file-text" size="s" />
                <FormattedMessage id="agent.snippets.snippet_created" /> (#1)
                <AgentAvatar agent={snippet.get('person')} />
                <span className="date"><FormattedRelative value={snippet.get('date_created')} /></span>
              </div>
            </div>
          }
        </Modal>
        {this.getComparisonModal()}
      </div>
    );
  }
}
