import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import Input from 'deskpro-styles/lib/Components/Input';
import Button from 'deskpro-styles/lib/Components/Button';
import SnippetsLabels from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsLabels';
import { SnippetsModalContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import { SnippetsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsList';
import { allSnippetsSelector } from '../Selectors/snippets';

@connect(state => ({
  snippets: allSnippetsSelector(state)
}))
export class SnippetsMenuContainer extends React.Component {
  static propTypes = {
    snippets:  PropTypes.object,
    closeMenu: PropTypes.func,
    type:      PropTypes.string
  };

  insertSnippet = (snippet) => {
    console.log(snippet);
  };

  render() {
    const { closeMenu, type } = this.props;
    const snippets = this.props.snippets.filter(snippet => snippet.get('types').includes(this.props.type));
    return (
      <SnippetsMenu
        closeMenu={closeMenu}
        insertSnippet={this.insertSnippet}
        snippets={snippets}
        type={type}
        langId={window.DP_PERSON_LANG_ID}
      />
    );
  }
}

export class SnippetsMenu extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    langId:        PropTypes.number,
    closeMenu:     PropTypes.func,
    insertSnippet: PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: '',
      editOpen:      false,
      snippetEdit:   {},
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
      closeModal={this.closeEditSnippet}
    />);
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
    const snippet = Immutable.fromJS({});
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
    const { snippets, closeMenu, langId, insertSnippet } = this.props;
    return (
      <div id="snippets__menu">
        <div className="header">
          <div className="search">
            <Isvg
              className="search"
              src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/search.svg`}
            />
            <Input className="input--large" ref={(c) => { this.searchInput = c; }} />
            <i className="fa fa-star-o favorite" />
            <a className="close-icon" onClick={closeMenu}>
              <Isvg
                className="close-icon"
                src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
              />
            </a>
          </div>
          <div className="top">
            <h1>Snippets</h1> <span className="count">({snippets.size})</span>
            {/* <Select />*/}
            <Button
              className="dp-button--secondary add-snippet"
              onClick={this.newSnippet}
            >
              + Snippet
            </Button>
          </div>
        </div>
        <div className="body">
          <SnippetsLabels
            snippets={snippets}
            selectLabel={this.selectLabel}
            selectedLabel={this.state.selectedLabel}
          />
          <SnippetsList
            snippets={snippets}
            langId={langId}
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
