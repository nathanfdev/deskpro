import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import Input from 'deskpro-styles/lib/Components/Input';
import Button from 'deskpro-styles/lib/Components/Button';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import SnippetsLabels from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsLabels';
import { SnippetsModalContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import { SnippetsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsList';
import { allSnippetsSelector, allSnippetBlobsSelector } from '../Selectors/snippets';

@connect(state => ({
  languages: allSelectorFactory('Language')(state),
  snippets:  allSnippetsSelector(state),
  blobs:     allSnippetBlobsSelector(state)
}))
export class SnippetsMenuContainer extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    blobs:         PropTypes.object,
    languages:     PropTypes.object,
    closeMenu:     PropTypes.func,
    insertSnippet: PropTypes.func,
    type:          PropTypes.string,
    department:    PropTypes.number,
  };
  static defaultProps = {
    department: 0
  };

  constructor(props) {
    super(props);
    this.state = {
      filter: '',
    };
  }

  insertSnippet = (e, snippet, langId) => {
    e.preventDefault();
    e.stopPropagation();
    this.props.insertSnippet(snippet.toJS(), this.props.blobs.toJS(), langId);
  };

  handleFilter = (filter) => {
    this.setState({ filter });
  };

  render() {
    const { closeMenu, type, department, languages } = this.props;
    const snippets = this.props.snippets
      .filter(snippet => snippet.get('types').indexOf(type) !== -1)
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
        const langId = window.DP_PERSON_LANG_ID;
        const re = new RegExp(this.state.filter, 'i');
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
    ;
    return (
      <SnippetsMenu
        closeMenu={closeMenu}
        insertSnippet={this.insertSnippet}
        handleFilter={this.handleFilter}
        snippets={snippets}
        languages={languages}
        type={type}
        filter={this.state.filter}
        langId={window.DP_PERSON_LANG_ID}
        ref={(c) => { this.menu = c; }}
      />
    );
  }
}

export class SnippetsMenu extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    languages:     PropTypes.object,
    langId:        PropTypes.number,
    closeMenu:     PropTypes.func,
    insertSnippet: PropTypes.func,
    handleFilter:  PropTypes.func,
    type:          PropTypes.string,
    filter:        PropTypes.string,
  };
  static defaultProps = {
    handleFilter() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: '',
      editOpen:      false,
      snippetEdit:   {},
      labelFilter:   '',
      focusedId:     0,
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', () => {
      this.searchInput.focus();
    });
  };

  componentDidMount = () => {
    setTimeout(() => this.searchInput.focus(), 500);
  };

  onSearchFocus = () => {
    this.focusFirst();
  };

  onSearchBlur = () => {
    this.setState({
      focusedId: 0
    });
  };

  getEditSnippet = () => {
    if (!this.state.editOpen) {
      return null;
    }
    return (<SnippetsModalContainer
      snippet={this.state.snippetEdit}
      langId={this.props.langId}
      type={this.props.type}
      closeModal={this.closeEditSnippet}
    />);
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

  editSnippet = (snippet) => {
    this.setState({
      editOpen:    true,
      snippetEdit: snippet,
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
    this.setState({
      focusedId: this.props.snippets.toSeq().slice(this.focusedIndex).first().get('id')
    });
  };

  focusNext = () => {
    if (this.focusedIndex < this.props.snippets.size - 1) {
      this.focusedIndex += 1;
    }
    this.setState({
      focusedId: this.props.snippets.slice(this.focusedIndex).first().get('id')
    });
  };

  selectFocused = (e) => {
    const snippet = this.props.snippets.toSeq().slice(this.focusedIndex).first();
    this.props.insertSnippet(e, snippet, this.props.langId);
  };

  render() {
    const { snippets, languages, closeMenu, langId, insertSnippet, filter, handleFilter } = this.props;
    return (
      <div id="snippets__menu">
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
            {/* <Select />*/}
            <Button
              className="dp-button--secondary add-snippet"
              onClick={this.newSnippet}
            >
              + {agentPhrases.get('agent.general.snippet')}
            </Button>
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
            langId={langId}
            labelFilter={this.state.labelFilter}
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
