import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import debounce from 'lodash/function/debounce';
import { Input } from 'deskpro-components/lib/Components/Forms';
import { Button } from 'deskpro-components/lib/Components/Buttons';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import SnippetsFiltering from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsFiltering';
import { SnippetsModalContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import { SnippetsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsList';
import * as actions from '../Actions/snippetsActions';
import { allSnippetsSelector, allSnippetBlobsSelector } from '../Selectors/snippets';
import { LanguageSelect } from './Menus/LanguageSelect';

@connect(state => ({
  me:        meSelector(state),
  languages: allSelectorFactory('Language')(state),
  snippets:  allSnippetsSelector(state),
  blobs:     allSnippetBlobsSelector(state)
}), null, null, { withRef: true })
export class SnippetsMenuContainer extends React.Component {
  static propTypes = {
    me:            PropTypes.object,
    snippets:      PropTypes.object,
    blobs:         PropTypes.object,
    languages:     PropTypes.object,
    closeMenu:     PropTypes.func,
    insertSnippet: PropTypes.func,
    dispatch:      PropTypes.func,
    open:          PropTypes.bool,
    type:          PropTypes.string,
    department:    PropTypes.number,
    width:         PropTypes.number,
    langId:        PropTypes.number,
  };
  static defaultProps = {
    department: 0,
    langId:     window.DP_PERSON_LANG_ID
  };

  static defaultLangPref = new Set(['context', 'agent', 'helpdesk']);

  static getSnippetTranslationToUse(snippetTranslations, langPref) {
    for (let i = 0; i < langPref.length; i++) {
      const found = snippetTranslations.find(tr => tr.get('language') === langPref[i]);
      if (found) {
        return found;
      }
    }
    return null;
  }

  constructor(props) {
    super(props);
    this.state = {
      filter:   '',
      showMode: 'all',
      langPref: SnippetsMenuContainer.defaultLangPref
    };
    this.props.dispatch(actions.loadSnippetLanguagePreferences())
      .then((data) => {
        const values = data.value.map((l) => {
          if (l.match(/^\d+$/)) {
            return parseInt(l, 10);
          }
          return l;
        });
        this.setState({ langPref: new Set(values) });
      },
        (reason) => {
          if (reason.data && reason.data.status === 404) {
            this.props.dispatch(actions.createSnippetLanguagePreferences([...SnippetsMenuContainer.defaultLangPref]));
          }
        });
    this.debouncedSavePrefs = debounce((value) => {
      this.props.dispatch(actions.saveSnippetLanguagePreferences([...value]));
    }, 1000);
  }

  shouldComponentUpdate(nextProps, nextState) {
    if (!nextProps.snippets.equals(this.props.snippets)) {
      return true;
    }
    if (!nextProps.blobs.equals(this.props.blobs)) {
      return true;
    }
    if (!nextProps.languages.equals(this.props.languages)) {
      return true;
    }
    if (nextProps.width !== this.props.width) {
      return true;
    }
    if (nextProps.langId !== this.props.langId) {
      return true;
    }
    if (nextProps.type !== this.props.type) {
      return true;
    }
    if (nextState.filter !== this.state.filter) {
      return true;
    }
    if (nextState.showMode !== this.state.showMode) {
      return true;
    }
    if (nextState.langPref !== this.state.langPref) {
      return true;
    }
    return nextProps.department !== this.props.department;
  }

  componentWillUnmount() {
    this.debouncedSavePrefs.flush();
  }

  onClose = () => {
    this.menu.onClose();
  };

  updateLanguagePref = (newPrefs) => {
    this.setState({
      langPref: newPrefs
    });
    this.forceUpdate();
    this.debouncedSavePrefs(newPrefs);
  };

  insertSnippet = (e, snippet, langId) => {
    e.preventDefault();
    e.stopPropagation();

    this.props.insertSnippet(snippet.toJS(), this.props.blobs.toJS(), langId);
  };

  handleFilter = (filter) => {
    this.setState({ filter });
  };

  handleShowMode = (checked, showMode) => {
    this.setState({ showMode });
  };

  render() {
    const { me, closeMenu, type, department, languages, width, langId, open } = this.props;
    const langContext = [langId, window.DP_PERSON_LANG_ID, window.DP_DEFAULT_LANG_ID];
    const langDisplay = [...this.state.langPref].map((lang) => {
      if (lang === 'context') {
        return langId;
      }
      if (lang === 'agent') {
        return window.DP_PERSON_LANG_ID;
      }
      if (lang === 'helpdesk') {
        return window.DP_DEFAULT_LANG_ID;
      }
      return lang;
    });
    let labels = new Set();
    this.props.snippets.map(snippet => snippet.get('labels').forEach(label => labels.add(label)));
    labels = Array.from(labels);
    const filteredSnippets = this.props.snippets
      .filter((snippet) => {
        if (snippet.get('types').indexOf(type) === -1) {
          return false;
        }
        if (!snippet.get('translations').find(translation =>
            langDisplay.indexOf(translation.get('language')) !== -1
          )) {
          return false;
        }
        if (department !== 0
          && !snippet.get('is_visible_global')
          && !snippet.get('visible_departments', []).find(d => d === department)
        ) {
          return false;
        }
        if (!this.state.filter) {
          return true;
        }
        const re = new RegExp(this.state.filter, 'i');
        return snippet.get('labels').find(label => label.match(re))
          || snippet.get('title').match(re)
          || snippet.get('shortcut_code').match(re)
          || SnippetsMenuContainer
              .getSnippetTranslationToUse(snippet.get('translations'), langContext)
            .get('content').match(re);
      });

    const snippets = filteredSnippets.filter((snippet) => {
      switch (this.state.showMode) {
        case 'all':
          return !snippet.get('is_draft', false);
        case 'my_snippets':
          return snippet.get('person') === me.get('id') && !snippet.get('is_draft', false);
        case 'my_team': {
          const myTeams = me.get('teams', new Immutable.List());
          return !snippet.get('is_draft', false) &&
            snippet.get('ownership_teams', new Immutable.List())
                .count(team => myTeams.find(t => t === team)) > 0;
        }
        case 'my_drafts':
          return snippet.get('is_draft', false) && snippet.get('person') === me.get('id');
        case 'all_drafts':
          return snippet.get('is_draft', false);
        default:
          return true;
      }
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
    ;
    return (
      <SnippetsMenu
        me={me}
        closeMenu={closeMenu}
        insertSnippet={this.insertSnippet}
        handleFilter={this.handleFilter}
        handleShowMode={this.handleShowMode}
        snippets={snippets}
        labels={labels}
        filteredSnippets={filteredSnippets}
        languages={languages}
        type={type}
        filter={this.state.filter}
        showMode={this.state.showMode}
        langId={langId}
        langDisplay={langDisplay}
        langContext={langContext}
        langPref={this.state.langPref}
        updateLanguagePref={this.updateLanguagePref}
        open={open}
        width={width}
        ref={(c) => { this.menu = c; }}
      />
    );
  }
}

export class SnippetsMenu extends React.Component {
  static propTypes = {
    me:                 PropTypes.object,
    snippets:           PropTypes.object,
    labels:             PropTypes.array,
    filteredSnippets:   PropTypes.object,
    languages:          PropTypes.object,
    langId:             PropTypes.number,
    width:              PropTypes.number,
    closeMenu:          PropTypes.func,
    insertSnippet:      PropTypes.func,
    handleFilter:       PropTypes.func,
    handleShowMode:     PropTypes.func,
    open:               PropTypes.bool,
    type:               PropTypes.string,
    filter:             PropTypes.string,
    showMode:           PropTypes.string,
    langContext:        PropTypes.array,
    langDisplay:        PropTypes.array,
    langPref:           PropTypes.object,
    updateLanguagePref: PropTypes.func,
  };
  static defaultProps = {
    handleFilter() {},
    handleShowMode() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: '',
      multiLabels:   [],
      multiMode:     'any',
      editOpen:      false,
      snippetEdit:   {},
      labelFilter:   '',
      focusedIndex:  null,
      editLang:      this.props.langId,
      height:        0,
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', () => {
      this.searchInput.focus();
      setTimeout(() => window.document.addEventListener('keydown', this.closeShortCut), 500);
      window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    });
    window.document.addEventListener('keydown', this.closeShortCut);
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  };

  componentDidMount = () => {
    setTimeout(() => this.searchInput.focus(), 500);
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
  };

  componentWillUnmount = () => {
    window.document.removeEventListener('keydown', this.closeShortCut);
    window.removeEventListener('resize', this.updateWindowDimensions);
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  };

  onSearchFocus = () => {
    this.focusFirst();
  };

  onSearchBlur = () => {
    this.setState({
      focusedIndex: null
    });
  };

  onClose = () => {
    window.document.removeEventListener('keydown', this.closeShortCut);
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  };

  getEditSnippet = () => {
    if (!this.state.editOpen) {
      return null;
    }
    return (
      <SnippetsModalContainer
        snippet={this.state.snippetEdit}
        langId={this.state.editLang}
        type={this.props.type}
        height={this.state.height}
        labelsSource={this.props.labels}
        closeModal={this.closeEditSnippet}
      />
    );
  };

  updateWindowDimensions = () => {
    this.setState({
      height: window.innerHeight
    });
  };

  closeShortCut = (ev) => {
    if (!this.props.open) {
      return;
    }

    if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac || ev.altKey && !DeskPRO_Window.keyboardShortcuts.isMac) { // eslint-disable-line no-undef
      if (ev.key === 's') {
        this.props.closeMenu();
      }
    }
  };

  handleLabelFilter = (value) => {
    this.setState({
      labelFilter: value
    });
  };

  handleMultiLabels = (checked, value) => {
    let multiLabels = this.state.multiLabels;
    if (checked) {
      multiLabels.push(value);
    } else {
      multiLabels = multiLabels.filter(e => e !== value);
    }
    this.setState(
      {
        multiLabels,
        selectedLabel: ''
      }
    );
  };

  selectLabel = (e, label) => {
    e.stopPropagation();
    if (this.state.multiLabels.length === 0) {
      this.setState({
        selectedLabel: label
      });
    } else {
      const checked = this.state.multiLabels.indexOf(label) === -1;
      this.handleMultiLabels(checked, label);
    }
  };

  clearLabels = () => {
    this.setState({
      multiLabels:   [],
      selectedLabel: '',
    });
  };

  selectMultiMode = (checked, multiMode) => {
    this.setState({
      multiMode
    });
  };

  editSnippet = (snippet, langId) => {
    this.setState({
      editOpen:    true,
      snippetEdit: snippet,
      editLang:    langId
    });
  };

  newSnippet = () => {
    const isOwnershipGlobal = (window.DESKPRO_PERSON_PERMS['agent_snippets.create_snippet']
      || window.DESKPRO_PERSON_PERMS['agent_snippets.create_global_snippet']);
    const snippet = Immutable.fromJS({
      is_visible_global:   true,
      is_ownership_global: isOwnershipGlobal,
      translations:        [],
      type:                [this.props.type],
    });
    this.setState({
      editOpen:    true,
      snippetEdit: snippet,
    });
  };

  closeEditSnippet = () => {
    this.setState({
      editOpen:    false,
      snippetEdit: {},
    });
  };

  handleSearchKeyDown = (event) => {
    this.closeShortCut(event);
    switch (event.key) {
      case 'Enter':
        this.selectFocused(event);
        break;
      case 'Escape':
        this.props.closeMenu();
        break;
      case 'ArrowUp':
        this.focusPrevious();
        break;
      case 'ArrowDown':
        this.focusNext();
        break;
      case 'Esc':
        this.props.closeMenu();
        break;
      default:
        return;
    }
    event.preventDefault();
  };

  focusFirst = () => {
    if (this.props.snippets.size) {
      this.setState({
        focusedIndex: 0
      });
      this.focusedIndex = 0;
    } else {
      this.setState({
        focusedIndex: null
      });
    }
  };

  focusPrevious = () => {
    if (this.focusedIndex > 0) {
      this.focusedIndex -= 1;
    }
    this.setState({
      focusedIndex: this.focusedIndex
    });
  };

  focusNext = () => {
    if (this.focusedIndex < this.props.snippets.size - 1) {
      this.focusedIndex += 1;
    }
    this.setState({
      focusedIndex: this.focusedIndex
    });
  };

  selectFocused = (e) => {
    const snippet = this.props.snippets.toSeq().slice(this.focusedIndex).first();
    const langId = SnippetsMenuContainer
      .getSnippetTranslationToUse(snippet.get('translations'), this.props.langPref)
      .get('language');
    this.props.insertSnippet(e, snippet, langId);
  };

  render() {
    const {
      me,
      snippets,
      filteredSnippets,
      languages,
      closeMenu,
      insertSnippet,
      filter,
      handleFilter,
      width,
      langContext,
      langDisplay,
      langPref,
      updateLanguagePref,
      showMode,
      handleShowMode,
    } = this.props;
    const style = {};
    if (width) {
      style.width = width - 5;
    }
    return (
      <div id="snippets__menu" style={style}>
        <div className="header">
          <div className="search">
            <Isvg
              className="search"
              src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/search.svg`}
            />
            <Input
              className="input--large"
              ref={(c) => { this.searchInput = c; }}
              value={filter}
              onChange={handleFilter}
              onFocus={this.onSearchFocus}
              onBlur={this.onSearchBlur}
              onKeyDown={this.handleSearchKeyDown}
            />
            <a className="close-icon" onClick={closeMenu}>
              <Isvg
                className="close-icon"
                src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
              />
            </a>
          </div>
          <div className="top">
            <h1>{agentPhrases.get('agent.general.snippets')}</h1> <span className="count">({snippets.size})</span>
            { (
              window.DESKPRO_PERSON_PERMS['agent_snippets.create_snippet']
              || window.DESKPRO_PERSON_PERMS['agent_snippets.create_self_snippet']
              || window.DESKPRO_PERSON_PERMS['agent_snippets.create_team_snippet']
              || window.DESKPRO_PERSON_PERMS['agent_snippets.create_global_snippet']
            ) ?
              <Button
                className="dp-button--primary add-snippet"
                onClick={this.newSnippet}
                size="medium"
              >
                + {agentPhrases.get('agent.general.snippet')}
              </Button>
              : null}
            <LanguageSelect
              languages={languages}
              langContext={langContext}
              langPref={langPref}
              onChange={updateLanguagePref}
            />
          </div>
        </div>
        <div className="body">
          <SnippetsFiltering
            me={me}
            snippets={snippets}
            filteredSnippets={filteredSnippets}
            clearLabels={this.clearLabels}
            selectLabel={this.selectLabel}
            selectMultiMode={this.selectMultiMode}
            labelFilter={this.state.labelFilter}
            handleLabelFilter={this.handleLabelFilter}
            onMultiLabelsChange={this.handleMultiLabels}
            selectedLabel={this.state.selectedLabel}
            multiLabels={this.state.multiLabels}
            multiMode={this.state.multiMode}
            showMode={showMode}
            height={this.state.height}
            handleShowMode={handleShowMode}
          />
          <SnippetsList
            me={me}
            snippets={snippets}
            languages={languages}
            langPref={langDisplay}
            filter={filter}
            height={this.state.height}
            width={width}
            focusedIndex={this.state.focusedIndex}
            selectedLabel={this.state.selectedLabel}
            multiLabels={this.state.multiLabels}
            multiMode={this.state.multiMode}
            editSnippet={this.editSnippet}
            insertSnippet={insertSnippet}
          />
        </div>
        {this.getEditSnippet()}
      </div>
    );
  }
}
