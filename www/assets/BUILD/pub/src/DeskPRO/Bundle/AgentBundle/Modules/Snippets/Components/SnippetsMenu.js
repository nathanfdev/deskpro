import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Input from 'deskpro-styles/lib/Components/Input';
import Button from 'deskpro-styles/lib/Components/Button';
import SnippetsLabels from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsLabels';
import { SnippetsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsList';
import * as actions from '../Actions/snippetsActions';

@connect(state => ({
  snippets: state.Snippets.snippets
}))
export class SnippetsMenuContainer extends React.Component {
  static propTypes = {
    snippets: PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  componentWillMount = () => {
    this.props.dispatch(actions.loadTicketSnippets());
  };

  render() {
    return (
      <SnippetsMenu snippets={this.props.snippets} />
    );
  }
}

export class SnippetsMenu extends React.Component {
  static propTypes = {
    snippets: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLabel: ''
    };
  }

  componentDidMount = () => {
    this.searchInput.focus();
  };

  selectLabel = (label) => {
    this.setState({
      selectedLabel: label
    });
  };

  render() {
    const { snippets } = this.props;
    return (
      <div id="snippets__menu">
        <div className="header">
          <div className="search">
            <i className="fa fa-search" />
            <Input className="input--large" ref={(c) => { this.searchInput = c; }} />
            <i className="fa fa-star-o favorite" />
          </div>
          <div className="top">
            <h1>Snippets</h1> <span className="count">({snippets.get('snippets').size})</span>
            {/* <Select />*/}
            <Button
              className="dp-button--secondary add-snippet"
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
            selectedLabel={this.state.selectedLabel}
          />
        </div>
      </div>
    );
  }
}
