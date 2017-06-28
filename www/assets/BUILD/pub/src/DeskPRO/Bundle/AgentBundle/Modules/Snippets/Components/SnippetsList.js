import React, { PropTypes } from 'react';
import striptags from 'striptags';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import Label from 'deskpro-styles/lib/Components/Label';

export class SnippetsListElement extends React.Component {
  static propTypes = {
    snippet:       PropTypes.object,
    languages:     PropTypes.object,
    editSnippet:   PropTypes.func,
    insertSnippet: PropTypes.func,
    langId:        PropTypes.number,
  };

  static defaultProps = {
    editSnippet() {}
  };

  constructor(props) {
    super(props);
    this.getContent   = this.getContent.bind(this);
    this.getDraft     = this.getDraft.bind(this);
    this.getLabels    = this.getLabels.bind(this);
    this.getLanguages = this.getLanguages.bind(this);
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
        return <div className="labels">Labels: {labels}</div>;
      }
    }
    return null;
  }

  getLanguages() {
    const { snippet, languages } = this.props;
    if (snippet.get('translations')) {
      const flags = [];
      snippet.get('translations').forEach((translation, key) => {
        const language = languages.find(l => l.get('id') === translation.get('language'));
        if (language.get('flag_image')) {
          flags.push(<img key={key} src={language.get('flag_image')} alt={language.get('title')} />);
        }
      });
      if (flags.length) {
        return <div className="languages">{flags}</div>;
      }
    }
    return null;
  }

  getContent() {
    const { snippet, langId } = this.props;
    const translation = snippet.get('translations').find(element => element.get('language') === langId);
    if (translation) {
      return striptags(translation.get('content'));
    }
    return null;
  }

  render() {
    const { snippet, editSnippet, insertSnippet } = this.props;
    return (
      <div className="snippet_list_element_wrapper">
        <div className="snippets__list__element" onClick={() => insertSnippet(snippet)}>
          {this.getDraft()}
          <span className="title">{snippet.get('title')} </span>
          <span className="shortcode dp-code">{`%${snippet.get('shortcut_code')}%`}</span><br />
          {this.getLanguages()}
          {this.getLabels()}
          <span className="content">{this.getContent()}</span>
        </div>
        <i className="fa fa-pencil edit-snippet" onClick={() => editSnippet(snippet)} />
      </div>
    );
  }
}
export class SnippetsList extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    languages:     PropTypes.object,
    selectedLabel: PropTypes.string,
    labelFilter:   PropTypes.string,
    filter:        PropTypes.string,
    langId:        PropTypes.number,
    editSnippet:   PropTypes.func,
    insertSnippet: PropTypes.func,
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
    const { snippets, languages, selectedLabel, filter, labelFilter, langId, editSnippet, insertSnippet } = this.props;
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
      .filter((snippet) => {
        if (!labelFilter) {
          return true;
        }
        const re = new RegExp(labelFilter, 'i');
        return snippet.get('labels').find(label => label.match(re));
      })
      .filter((snippet) => {
        if (!filter) {
          return true;
        }
        const re = new RegExp(filter, 'i');
        return snippet.get('labels').find(label => label.match(re))
          || snippet.get('title').match(re)
          || snippet.get('shortcut_code').match(re)
          || snippet.get('translations').find(element => element.get('language') === langId).get('content').match(re);
      })
      .sort((a, b) => {
        const titleA = a.get('title').toLowerCase();
        const titleB = b.get('title').toLowerCase();
        if (titleA > titleB) {
          return 1;
        } else if (titleA < titleB) {
          return -1;
        }
        return 0;
      })
      .forEach((element) => {
        elements.push(
          <SnippetsListElement
            key={element.get('id')}
            snippet={element}
            languages={languages}
            langId={langId}
            editSnippet={editSnippet}
            insertSnippet={insertSnippet}
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
