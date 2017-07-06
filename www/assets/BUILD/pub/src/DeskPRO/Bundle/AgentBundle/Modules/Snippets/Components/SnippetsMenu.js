import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import { Input, Select } from 'deskpro-components/lib/Components/Forms';
import Button from 'deskpro-components/lib/Components/Button';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import SnippetsLabels from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsLabels';
import { SnippetsModalContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import { SnippetsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsList';
import { allSnippetsSelector, allSnippetBlobsSelector } from '../Selectors/snippets';

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
    open:          PropTypes.bool,
    type:          PropTypes.string,
    department:    PropTypes.number,
    width:         PropTypes.number,
    langId:        PropTypes.number,
  };
  static defaultProps = {
    department: 0,
    langId:     window.DP_PERSON_LANG_ID
  };;

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
    };
  }

  onClose = () => {
    this.menu.onClose();
  };

  insertSnippet = (e, snippet, langId) => {
    e.preventDefault();
    e.stopPropagation();
    this.props.insertSnippet(snippet.toJS(), this.props.blobs.toJS(), langId);
  };

  handleFilter = (filter) => {
    this.setState({ filter });
  };

  handleShowMode = (showMode) => {
    this.setState({ showMode });
  };

  render() {
    const { me, closeMenu, type, department, languages, width, langId, open } = this.props;
    const langPref = [langId, window.DP_PERSON_LANG_ID, window.DP_DEFAULT_LANG_ID];
    const snippets = this.props.snippets
      .filter(snippet => snippet.get('types').indexOf(type) !== -1)
      .filter(snippet => snippet.get('translations').find(translation =>
        langPref.indexOf(translation.get('language')) !== -1
      ))
      .filter((snippet) => {
        if (department === 0) {
          return true;
        }
        if (snippet.get('is_visible_global')) {
          return true;
        }
        return snippet.get('visible_departments', []).find(d => d === department);
      })
      .filter((snippet) => {
        if (!this.state.filter) {
          return true;
        }
        const re = new RegExp(this.state.filter, 'i');
        return snippet.get('labels').find(label => label.match(re))
          || snippet.get('title').match(re)
          || snippet.get('shortcut_code').match(re)
          || SnippetsMenuContainer.getSnippetTranslationToUse(snippet.get('translations'), langPref).get('content').match(re);
      })
      .filter((snippet) => {
        switch (this.state.showMode) {
          case 'all':
            return true;
          case 'my_snippets':
            return snippet.get('person') === me.get('id');
          case 'my_team': {
            const myTeams = me.get('teams', new Immutable.List());
            return snippet.get('ownership_teams', new Immutable.List())
                .filter(team => myTeams.find(t => t === team)).size > 0;
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
        languages={languages}
        type={type}
        filter={this.state.filter}
        showMode={this.state.showMode}
        langId={langId}
        langPref={langPref}
        open={open}
        width={width}
        ref={(c) => { this.menu = c; }}
      />
    );
  }
}

export class SnippetsMenu extends React.Component {
  static propTypes = {
    me:             PropTypes.object,
    snippets:       PropTypes.object,
    languages:      PropTypes.object,
    langId:         PropTypes.number,
    width:          PropTypes.number,
    closeMenu:      PropTypes.func,
    insertSnippet:  PropTypes.func,
    handleFilter:   PropTypes.func,
    handleShowMode: PropTypes.func,
    open:           PropTypes.bool,
    type:           PropTypes.string,
    filter:         PropTypes.string,
    showMode:       PropTypes.string,
    langPref:       PropTypes.array,
  };
  static defaultProps = {
    handleFilter() {},
    handleShowMode() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: '',
      editOpen:      false,
      snippetEdit:   {},
      labelFilter:   '',
      focusedId:     0,
      editLang:      this.props.langId,
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', () => {
      this.searchInput.focus();
      setTimeout(() => window.document.addEventListener('keydown', this.closeShortCut), 500);
    });
    window.document.addEventListener('keydown', this.closeShortCut);
  };

  componentDidMount = () => {
    setTimeout(() => this.searchInput.focus(), 500);
  };

  componentWillUnmount = () => {
    window.document.removeEventListener('keydown', this.closeShortCut);
  };

  onSearchFocus = () => {
    this.focusFirst();
  };

  onSearchBlur = () => {
    this.setState({
      focusedId: 0
    });
  };

  onClose = () => {
    window.document.removeEventListener('keydown', this.closeShortCut);
  };

  getEditSnippet = () => {
    if (!this.state.editOpen) {
      return null;
    }
    return (<SnippetsModalContainer
      snippet={this.state.snippetEdit}
      langId={this.state.editLang}
      type={this.props.type}
      closeModal={this.closeEditSnippet}
    />);
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

  selectLabel = (label) => {
    this.setState({
      selectedLabel: label
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
    const snippet = Immutable.fromJS({
      is_visible_global:   true,
      is_ownership_global: true,
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
    switch (event.keyCode) {
      case 13: // enter
        this.selectFocused(event);
        break;
      case 27: // escape
        this.props.closeMenu();
        break;
      case 38: // up
        this.focusPrevious();
        break;
      case 40: // down
        this.focusNext();
        break;
      default:
        setTimeout(() => this.focusFirst(), 300);
        return;
    }
    event.preventDefault();
  };

  focusFirst = () => {
    if (this.props.snippets.size) {
      this.setState({
        focusedId: this.props.snippets.first().get('id')
      });
      this.focusedIndex = 0;
    } else {
      this.setState({
        focusedId: 0
      });
    }
  };

  focusPrevious = () => {
    if (this.focusedIndex > 0) {
      this.focusedIndex -= 1;
    }
    let i = 0;
    this.props.snippets.forEach((snippet) => {
      if (i === this.focusedIndex) {
        this.setState({
          focusedId: snippet.get('id')
        });
        return false;
      }
      i += 1;
      return true;
    });
  };

  focusNext = () => {
    if (this.focusedIndex < this.props.snippets.size - 1) {
      this.focusedIndex += 1;
    }
    let i = 0;
    this.props.snippets.forEach((snippet) => {
      if (i === this.focusedIndex) {
        this.setState({
          focusedId: snippet.get('id')
        });
        return false;
      }
      i += 1;
      return true;
    });
  };

  selectFocused = (e) => {
    const snippet = this.props.snippets.toSeq().slice(this.focusedIndex).first();
    const langId = SnippetsMenuContainer.getSnippetTranslationToUse(snippet.get('translations'), this.props.langPref).get('language');
    this.props.insertSnippet(e, snippet, langId);
  };

  render() {
    const { me, snippets, languages, closeMenu, insertSnippet, filter, handleFilter, width, langPref } = this.props;
    let showOptions = [
      { value: 'all', label: agentPhrases.get('agent.snippets.all_snippets') },
      { value: 'my_snippets', label: agentPhrases.get('agent.snippets.my_snippets') },
    ];
    if (me.get('teams').size > 1) {
      showOptions.push({ value: 'my_team', label: agentPhrases.get('agent.snippets.my_teams_snippets') });
    } else if (me.get('teams').size > 0) {
      showOptions.push({ value: 'my_team', label: agentPhrases.get('agent.snippets.my_team_snippets') });
    }
    showOptions = showOptions.concat([
      { value: 'my_drafts', label: agentPhrases.get('agent.snippets.my_drafts') },
      { value: 'all_drafts', label: agentPhrases.get('agent.snippets.all_drafts') },
    ]);
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
            <Button
              className="dp-button--secondary add-snippet"
              onClick={this.newSnippet}
            >
              + {agentPhrases.get('agent.general.snippet')}
            </Button>
            <Select
              placeholder={agentPhrases.get('agent.general.show')}
              searchable={false}
              clearable={false}
              simpleValue
              value={this.props.showMode}
              onChange={this.props.handleShowMode}
              className="show-mode"
              options={showOptions}
            />
          </div>
        </div>
        <div className="body">
          <SnippetsLabels
            snippets={snippets}
            selectLabel={this.selectLabel}
            labelFilter={this.state.labelFilter}
            handleLabelFilter={this.handleLabelFilter}
            selectedLabel={this.state.selectedLabel}
          />
          <SnippetsList
            snippets={snippets}
            languages={languages}
            langPref={langPref}
            filter={filter}
            focusedId={this.state.focusedId}
            selectedLabel={this.state.selectedLabel}
            editSnippet={this.editSnippet}
            insertSnippet={insertSnippet}
          />
        </div>
        {this.getEditSnippet()}
      </div>
    );
  }
}
