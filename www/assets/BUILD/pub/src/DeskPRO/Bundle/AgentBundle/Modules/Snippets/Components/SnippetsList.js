import React, { PropTypes } from 'react';
import classNames from 'classnames';
import striptags from 'striptags';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { Label } from 'deskpro-components/lib/Components/Forms';

export class SnippetsListElement extends React.Component {
  static propTypes = {
    snippet:       PropTypes.object,
    languages:     PropTypes.object,
    editSnippet:   PropTypes.func,
    insertSnippet: PropTypes.func,
    focused:       PropTypes.bool,
    langPref:      PropTypes.array,
  };
  static defaultProps = {
    focused: false,
    editSnippet() {},
  };

  constructor(props) {
    super(props);
    this.getContent   = this.getContent.bind(this);
    this.getDraft     = this.getDraft.bind(this);
    this.getLabels    = this.getLabels.bind(this);
    this.getLanguages = this.getLanguages.bind(this);
    this.findLanguage = this.findLanguage.bind(this);
  }

  getDraft() {
    const { snippet } = this.props;
    if (snippet.get('is_draft')) {
      return <span className="draft"><i className="fa fa-file-o" />{agentPhrases.get('agent.general.draft')}</span>;
    }
    return null;
  }

  getLabels() {
    const { snippet } = this.props;
    if (snippet.get('labels')) {
      const labels = [];
      snippet.get('labels').forEach((label, key) => {
        labels.push(<Label key={key}>{label} </Label>);
      });
      if (labels.length) {
        return <div className="labels"><i className="fa fa-tag" /> {labels}</div>;
      }
    }
    return null;
  }

  getLanguages() {
    const { snippet, languages, insertSnippet } = this.props;
    if (snippet.get('translations')) {
      const flags = [];
      snippet.get('translations').forEach((translation, key) => {
        const language = languages.find(l => l.get('id') === translation.get('language'));
        if (language && language.get('flag_image')) {
          flags.push(
            <img
              key={key}
              src={language.get('flag_image')}
              alt={language.get('title')}
              title={language.get('title')}
              onClick={e => insertSnippet(e, snippet, language.get('id'))}
            />);
        }
      });
      if (flags.length) {
        return <div className="languages">{flags}</div>;
      }
    }
    return null;
  }

  getContent(langId) {
    const { snippet } = this.props;
    const translation = snippet.get('translations').find(element => element.get('language') === langId);
    if (translation && translation.get('content')) {
      return striptags(translation.get('content'));
    }
    return null;
  }

  getShortcutCode() {
    const { snippet } = this.props;
    if (snippet.get('shortcut_code')) {
      return <span className="shortcode dp-code">{`%${snippet.get('shortcut_code')}%`}</span>;
    }
    return null;
  }

  findLanguage() {
    const { snippet, langPref } = this.props;
    for (let i = 0; i < langPref.length; i++) {
      const translation = snippet.get('translations').find(element => element.get('language') === langPref[i]);
      if (translation && translation.get('content')) {
        return langPref[i];
      }
    }
    return null;
  }

  render() {
    const { snippet, editSnippet, insertSnippet, focused } = this.props;
    const langId = this.findLanguage();
    return (
      <div className={classNames('snippet_list_element_wrapper', { 'snippet_list_element_wrapper--focused': focused })}>
        <div className="snippets__list__element" onClick={e => insertSnippet(e, snippet, langId)} >
          {this.getDraft()}
          <span className="title">{snippet.get('title')} </span>
          {this.getShortcutCode()}<br />
          {this.getLanguages()}
          {this.getLabels()}
          <span className="content">{this.getContent(langId)}</span>
        </div>
        <div onClick={() => editSnippet(snippet, langId)} className="edit-snippet">
          <i className="fa fa-pencil" />
        </div>
      </div>
    );
  }
}
export class SnippetsList extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    languages:     PropTypes.object,
    selectedLabel: PropTypes.string,
    focusedId:     PropTypes.number,
    editSnippet:   PropTypes.func,
    insertSnippet: PropTypes.func,
    langPref:      PropTypes.array,
  };

  constructor(props) {
    super(props);
    this.state = {
      height: 0
    };
  }

  componentDidMount() {
    this.updateWindowDimensions();
    window.addEventListener('resize', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.updateWindowDimensions();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
    this.offsetTop = this.list.offsetTop;
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.updateWindowDimensions);
  }

  updateWindowDimensions = () => {
    this.setState({
      height: window.innerHeight
    });
  };

  renderElements = () => {
    const elements = [];
    const {
      snippets,
      languages,
      selectedLabel,
      editSnippet,
      insertSnippet,
      focusedId,
      langPref,
    } = this.props;
    if (!snippets) {
      return null;
    }
    snippets
      .filter((snippet) => {
        if (!selectedLabel) {
          return true;
        }
        return snippet.get('labels').find(label => label === selectedLabel);
      })
      .forEach((element) => {
        elements.push(
          <SnippetsListElement
            key={element.get('id')}
            snippet={element}
            languages={languages}
            langPref={langPref}
            editSnippet={editSnippet}
            insertSnippet={insertSnippet}
            focused={focusedId === element.get('id')}
          />
        );
      });
    if (elements.length === 0) {
      return (
        <div className="snippet_list_element_wrapper">
          {agentPhrases.get('agent.search.no_results_found')}
        </div>
      );
    }
    return elements;
  };

  render() {
    let height = this.state.height - this.offsetTop;
    if (isNaN(height)) {
      height = 0;
    }
    return (
      <div className="snippets__list" ref={(c) => { this.list = c; }} style={{ height }}>
        {this.renderElements()}
      </div>
    );
  }
}
