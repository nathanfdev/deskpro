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

  insertSnippet = (e, snippet, langId) => {
    e.preventDefault();
    e.stopPropagation();
    this.props.insertSnippet(snippet.toJS(), this.props.blobs.toJS(), langId);
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
    ;
    return (
      <SnippetsMenu
        closeMenu={closeMenu}
        insertSnippet={this.insertSnippet}
        snippets={snippets}
        languages={languages}
        type={type}
        langId={window.DP_PERSON_LANG_ID}
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
    type:          PropTypes.string,
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: '',
      editOpen:      false,
      snippetEdit:   {},
      filter:        '',
      labelFilter:   '',
    };
  }

  componentDidMount = () => {
    this.searchInput.focus();
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

  handleFilter = (filter) => {
    this.setState({ filter });
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
      is_visible_global: true,
      translations:      [],
      type:              [this.props.type],
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

  render() {
    const { snippets, languages, closeMenu, langId, insertSnippet } = this.props;
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
              value={this.state.filter}
              onChange={this.handleFilter}
            />
            <i className="fa fa-star-o favorite" />
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
            filter={this.state.filter}
            labelFilter={this.state.labelFilter}
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
