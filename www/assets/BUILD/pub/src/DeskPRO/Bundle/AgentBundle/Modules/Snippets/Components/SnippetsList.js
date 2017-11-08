import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import htmlToText from 'html-to-text';
import Highlighter from 'react-highlight-words';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { List } from 'react-virtualized';
import { Checkbox, Tag } from '@deskpro/react-components/lib/Components/Forms';
import Icon from '@deskpro/react-components/lib/Components/Icon';

export class SnippetsListElement extends React.PureComponent {
  static propTypes = {
    me:                  PropTypes.object,
    snippet:             PropTypes.object,
    languages:           PropTypes.object,
    editSnippet:         PropTypes.func,
    insertSnippet:       PropTypes.func,
    focused:             PropTypes.bool,
    massActionMode:      PropTypes.string,
    langPref:            PropTypes.array,
    filter:              PropTypes.string,
    type:                PropTypes.string,
    style:               PropTypes.object,
    selectForMassAction: PropTypes.func,
    massActionSelected:  PropTypes.bool,
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
    this.insertSnippet = this.insertSnippet.bind(this);
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
        labels.push(<Tag key={key}>{label} </Tag>);
      });
      if (labels.length) {
        return <div className="labels"><i className="fa fa-tag" /> {labels}</div>;
      }
    }
    return null;
  }

  getLanguages() {
    const { snippet, languages, langPref, type } = this.props;
    if (snippet.get('translations')) {
      const flags = [];
      const isSplit = snippet.get('is_split', false);
      snippet.get('translations').forEach((translation, key) => {
        if (langPref.indexOf(translation.get('language')) !== -1) {
          const language = languages.find(l =>
            l.get('id') === translation.get('language') && (!isSplit || translation.get('type') === type)
          );
          if (language && language.get('flag_image')) {
            flags.push(
              <img
                key={key}
                src={language.get('flag_image')}
                alt={language.get('title')}
                title={language.get('title')}
                onClick={e => this.insertSnippet(e, snippet, language.get('id'))}
              />);
          }
        }
      });
      if (flags.length) {
        return <div className="languages">{flags}</div>;
      }
    }
    return null;
  }

  getContent(langId) {
    const { snippet, filter, type } = this.props;
    const isSplit = snippet.get('is_split', false);
    const translation = snippet.get('translations').find(element =>
      element.get('language') === langId && (!isSplit || element.get('type') === type)
    );
    if (translation && translation.get('content')) {
      let content = htmlToText.fromString(translation.get('content'));
      const lines = [];
      if (filter && filter.length > 2) {
        const re = new RegExp(`([\\S\\s\\R\\n]{0,33})(${filter}([\\S\\s\\R\\n]*))$`, 'i');
        const matches = content.match(re);
        if (matches) {
          if (matches[1] && matches[1].length > 30) {
            matches[1] = `...${matches[1].slice(3)}`;
          }
          content = `${matches[1]}${matches[2]}`;
        }
        content.substring(0, 300).split(/\n/).forEach((line, key) => {
          lines.push(
            <span className="line" key={key}>
              <Highlighter
                highlightClassName="filter-highlight"
                searchWords={[filter]}
                textToHighlight={line}
              />
              <span className="line-break">&#8617; </span>
            </span>
          );
        });
      } else {
        content.substring(0, 300).split(/\n/).forEach((line, key) => {
          lines.push(<span className="line" key={key}>{line}<span className="line-break">&#8617; </span></span>);
        });
      }
      return lines;
    }
    return null;
  }

  getMassActions() {
    const { me, snippet, massActionMode, selectForMassAction, massActionSelected } = this.props;
    if (massActionMode
      && (window.DESKPRO_PERSON_PERMS['agent_snippets.edit_by_others'] || snippet.get('person') === me.get('id'))) {
      return (
        <Checkbox
          value={snippet.get('id')}
          checked={massActionSelected}
          stopPropagation
          onChange={selectForMassAction}
        />
      );
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

  getStats() {
    const { snippet } = this.props;
    return (<div className="stats">
      <span className="stat">{agentPhrases.get('agent.snippets.used')} <span className="value">{snippet.get('usage_count')}</span></span>
      <span className="stat">
        {agentPhrases.get('agent.snippets.feedback')}&nbsp;
        <span className="rating">
          <Icon name="smile-o" />
          <span className="value">{snippet.get('positive_ratings')}</span>
        </span>
        <span className="rating">
          <Icon name="meh-o" />
          <span className="value">{snippet.get('neutral_ratings')}</span>
        </span>
        <span className="rating">
          <Icon name="frown-o" />
          <span className="value">{snippet.get('negative_ratings')}</span>
        </span>
      </span>
    </div>);
  }

  findLanguage() {
    const { snippet, langPref } = this.props;
    for (let i = 0; i < langPref.length; i++) {
      const translation = snippet.get('translations').find(element => element.get('language') === langPref[i]);
      if (translation && translation.get('content')) {
        return langPref[i];
      }
    }
    return langPref[0];
  }

  insertSnippet(e, snippet, languageId) {
    const { me, massActionMode, massActionSelected, insertSnippet, selectForMassAction } = this.props;
    if (massActionMode !== '') {
      if ((window.DESKPRO_PERSON_PERMS['agent_snippets.edit_by_others'] || snippet.get('person') === me.get('id'))) {
        selectForMassAction(!massActionSelected, snippet.get('id'));
      }
    } else {
      insertSnippet(e, snippet, languageId);
    }
  }

  render() {
    const { me, snippet, editSnippet, focused, massActionMode, massActionSelected, style } = this.props;
    const langId = this.findLanguage();
    return (
      <div
        className={classNames(
          'snippet_list_element_wrapper',
          {
            'snippet_list_element_wrapper--focused':      focused || massActionSelected,
            'snippet_list_element_wrapper--mass-actions': massActionMode,
          }
        )}
        style={style}
      >
        {this.getMassActions()}
        <div className="snippets__list__element" onClick={e => this.insertSnippet(e, snippet, langId)} >
          {this.getDraft()}
          <span className="title">{snippet.get('title')} </span>
          {this.getShortcutCode()}<br />
          {this.getLanguages()}
          {this.getLabels()}
          <span className="content">{this.getContent(langId)}</span>
          {this.getStats()}
        </div>
        { window.DESKPRO_PERSON_PERMS['agent_snippets.edit_by_others'] || snippet.get('person') === me.get('id') ?
          <div onClick={() => editSnippet(snippet, langId)} className="edit-snippet">
            <i className="fa fa-pencil" />
          </div>
        : null}
      </div>
    );
  }
}
export class SnippetsList extends React.Component {
  static propTypes = {
    me:                  PropTypes.object,
    snippets:            PropTypes.object,
    languages:           PropTypes.object,
    selectedLabel:       PropTypes.string,
    multiMode:           PropTypes.string,
    multiLabels:         PropTypes.array,
    focusedIndex:        PropTypes.number,
    height:              PropTypes.number,
    width:               PropTypes.number,
    filter:              PropTypes.string,
    type:                PropTypes.string,
    editSnippet:         PropTypes.func,
    insertSnippet:       PropTypes.func,
    langPref:            PropTypes.array,
    massActionMode:      PropTypes.string,
    selectForMassAction: PropTypes.func,
    massActionsSelected: PropTypes.object,
  };

  static noRowsRenderer() {
    return (
      <div className="snippet_list_element_wrapper">
        {agentPhrases.get('agent.search.no_results_found')}
      </div>
    );
  }

  constructor(props) {
    super(props);
    this.rowRenderer = this.rowRenderer.bind(this);
  }

  componentDidMount() {
    this.offsetTop = this.listRef.offsetTop;
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.focusedIndex !== this.props.focusedIndex) {
      setTimeout(() => this.updateList(this.props.focusedIndex), 10);
    }
    if (nextProps.massActionsSelected !== this.props.massActionsSelected) {
      this.listRef.forceUpdateGrid();
    }
    if (!nextProps.snippets.equals(this.props.snippets)) {
      this.listRef.forceUpdateGrid();
    }
    if (nextProps.langPref !== this.props.langPref) {
      this.listRef.forceUpdateGrid();
    }
    if (nextProps.massActionMode !== this.props.massActionMode) {
      this.listRef.forceUpdateGrid();
    }
  }

  updateList(index) {
    this.listRef.forceUpdateGrid();
    if (index) {
      this.listRef.scrollToRow(index);
    }
  }

  rowRenderer({
    key,
    index,
    style
  }) {
    const {
      me,
      languages,
      editSnippet,
      insertSnippet,
      focusedIndex,
      langPref,
      filter,
      type,
      massActionMode,
      massActionsSelected,
      selectForMassAction,
    } = this.props;
    const snippet = this.list[index];
    if (!snippet) {
      return null;
    }
    return (
      <SnippetsListElement
        key={key}
        me={me}
        snippet={snippet}
        languages={languages}
        langPref={langPref}
        filter={filter}
        type={type}
        editSnippet={editSnippet}
        insertSnippet={insertSnippet}
        focused={focusedIndex === index}
        massActionMode={massActionMode}
        massActionSelected={massActionsSelected.has(snippet.get('id'))}
        selectForMassAction={selectForMassAction}
        style={style}
      />
    );
  }

  render() {
    const {
      snippets,
      selectedLabel,
      multiLabels,
      multiMode,
      width,
      massActionMode,
      selectForMassAction,
      massActionsSelected,
    } = this.props;
    let height = this.props.height;
    if (isNaN(height)) {
      height = 400;
    }
    this.list = snippets
      .filter((snippet) => {
        if (multiLabels.length) {
          if (multiMode === 'any') {
            return multiLabels.filter(checkedLabel => snippet.get('labels').find(label =>
                label.replace(/\s*\/\s*/, '/') === checkedLabel ||
                label.replace(/\s*\/\s*/, '/').match(`${checkedLabel}/`)
              )).length > 0;
          }
          return multiLabels.filter(checkedLabel => snippet.get('labels').find(label =>
              label.replace(/\s*\/\s*/, '/') === checkedLabel ||
              label.replace(/\s*\/\s*/, '/').match(`${checkedLabel}/`)
            )).length === multiLabels.length;
        }
        if (!selectedLabel) {
          return true;
        }
        return snippet.get('labels').find(label =>
          label.replace(/\s*\/\s*/, '/') === selectedLabel ||
          label.replace(/\s*\/\s*/, '/').match(`${selectedLabel}/`)
        );
      }).toArray();
    let listWidth = (width - 15) * 0.74;
    if (width > 928) {
      listWidth = width - 255;
    }
    if (massActionMode) {
      height -= 18;
    }
    return (
      <div className="snippets__list">
        { massActionMode ?
          <Checkbox
            value="select_all"
            stopPropagation
            onChange={(checked) => { selectForMassAction(checked, this.list); }}
            checked={this.list.filter(snippet => !massActionsSelected.has(snippet.get('id'))).length === 0}
          >
            {agentPhrases.get('agent.general.select_all')}
          </Checkbox>
          : null
        }
        <List
          className="snippets__list"
          height={height}
          width={listWidth}
          rowCount={this.list.length}
          rowHeight={83}
          rowRenderer={this.rowRenderer}
          noRowsRenderer={SnippetsList.noRowsRenderer}
          overscanRowCount={2}
          ref={(c) => { this.listRef = c; }}
        />
      </div>
    );
  }
}
